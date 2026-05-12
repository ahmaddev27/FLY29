<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\PointsHistory;
use App\Models\RedemptionRequest;
use App\Models\TierHistory;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Aggregations for the 5 admin reports:
 *   - Points     : awarded/redeemed totals + daily series
 *   - Sales      : volume + revenue + per-day series
 *   - Tiers      : current distribution + upgrade/downgrade movement
 *   - Redemptions: by status, type, fulfillment lag
 *   - Top Agents : leaderboard by sales / points / packages
 *
 * Every aggregation accepts a [from, to] date range (default last 30 days).
 */
class ReportService
{
    /* ------------------------------------------------------------ */
    /* Points report                                                */
    /* ------------------------------------------------------------ */

    public function pointsReport(array $range): array
    {
        [$from, $to] = $this->parseRange($range);

        $awarded = (int) PointsHistory::whereBetween('created_at', [$from, $to])
            ->where('points_delta', '>', 0)
            ->sum('points_delta');

        $deducted = (int) PointsHistory::whereBetween('created_at', [$from, $to])
            ->where('points_delta', '<', 0)
            ->sum('points_delta');

        $series = PointsHistory::whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE(created_at) as day, SUM(CASE WHEN points_delta > 0 THEN points_delta ELSE 0 END) as awarded, SUM(CASE WHEN points_delta < 0 THEN ABS(points_delta) ELSE 0 END) as redeemed")
            ->groupBy('day')->orderBy('day')->get();

        return [
            'range'    => compact('from', 'to'),
            'totals'   => [
                'awarded'  => $awarded,
                'redeemed' => abs($deducted),
                'net'      => $awarded + $deducted, // deducted is negative
            ],
            'series'   => $this->fillDays($series, $from, $to, ['awarded', 'redeemed']),
        ];
    }

    /* ------------------------------------------------------------ */
    /* Sales report                                                 */
    /* ------------------------------------------------------------ */

    public function salesReport(array $range): array
    {
        [$from, $to] = $this->parseRange($range);

        $totals = Transaction::whereBetween('transaction_date', [$from, $to])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount_usd), 0) as revenue, COALESCE(AVG(amount_usd), 0) as avg_value')
            ->first();

        $packages = (int) Transaction::whereBetween('transaction_date', [$from, $to])
            ->where('transaction_type', 'package')->count();

        $services = (int) Transaction::whereBetween('transaction_date', [$from, $to])
            ->where('transaction_type', 'service')->count();

        $series = Transaction::whereBetween('transaction_date', [$from, $to])
            ->selectRaw('DATE(transaction_date) as day, COUNT(*) as count, COALESCE(SUM(amount_usd), 0) as revenue')
            ->groupBy('day')->orderBy('day')->get();

        return [
            'range'  => compact('from', 'to'),
            'totals' => [
                'count'     => (int) $totals->count,
                'revenue'   => (float) $totals->revenue,
                'avg_value' => (float) $totals->avg_value,
                'packages'  => $packages,
                'services'  => $services,
            ],
            'series' => $this->fillDays($series, $from, $to, ['count', 'revenue']),
        ];
    }

    /* ------------------------------------------------------------ */
    /* Tiers report                                                 */
    /* ------------------------------------------------------------ */

    public function tiersReport(array $range): array
    {
        [$from, $to] = $this->parseRange($range);

        // Current distribution
        $current = Agent::selectRaw('current_tier, COUNT(*) as c')
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->groupBy('current_tier')->pluck('c', 'current_tier')->toArray();

        $current = array_merge(['bronze' => 0, 'silver' => 0, 'gold' => 0, 'diamond' => 0], $current);

        // Movement: upgrades and downgrades in range
        $upgrades   = TierHistory::whereBetween('created_at', [$from, $to])
            ->where('action', 'upgrade')->count();
        $downgrades = TierHistory::whereBetween('created_at', [$from, $to])
            ->where('action', 'downgrade')->count();
        $manual     = TierHistory::whereBetween('created_at', [$from, $to])
            ->where('action', 'manual')->count();

        // Per-tier upgrade destination breakdown
        $upgradeBreakdown = TierHistory::whereBetween('created_at', [$from, $to])
            ->where('action', 'upgrade')
            ->selectRaw('to_tier, COUNT(*) as c')
            ->groupBy('to_tier')->pluck('c', 'to_tier')->toArray();

        return [
            'range'    => compact('from', 'to'),
            'current'  => $current,
            'movement' => [
                'upgrades'   => $upgrades,
                'downgrades' => $downgrades,
                'manual'     => $manual,
                'breakdown'  => $upgradeBreakdown,
            ],
        ];
    }

    /* ------------------------------------------------------------ */
    /* Redemptions report                                           */
    /* ------------------------------------------------------------ */

    public function redemptionsReport(array $range): array
    {
        [$from, $to] = $this->parseRange($range);

        $baseQuery = fn () => RedemptionRequest::whereBetween('requested_at', [$from, $to]);

        $byStatus = $baseQuery()
            ->selectRaw('status, COUNT(*) as c, COALESCE(SUM(points), 0) as points, COALESCE(SUM(cash_value_usd), 0) as usd')
            ->groupBy('status')->get()->keyBy('status');

        $byType = $baseQuery()
            ->selectRaw('type, COUNT(*) as c, COALESCE(SUM(points), 0) as points')
            ->groupBy('type')->get()->keyBy('type');

        // Avg fulfillment lag in hours — SQL differs by driver.
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        $lagExpr = $driver === 'sqlite'
            ? 'AVG((julianday(fulfilled_at) - julianday(processed_at)) * 24)'
            : 'AVG(TIMESTAMPDIFF(MINUTE, processed_at, fulfilled_at) / 60)';

        $avgLagHours = $baseQuery()
            ->whereNotNull('fulfilled_at')
            ->whereNotNull('processed_at')
            ->selectRaw("{$lagExpr} as h")
            ->value('h');

        return [
            'range'       => compact('from', 'to'),
            'by_status'   => $byStatus,
            'by_type'     => $byType,
            'avg_lag_hours' => round((float) $avgLagHours, 1),
        ];
    }

    /* ------------------------------------------------------------ */
    /* Top agents report                                            */
    /* ------------------------------------------------------------ */

    public function topAgentsReport(array $range, string $orderBy = 'revenue', int $limit = 25): array
    {
        [$from, $to] = $this->parseRange($range);

        $agents = Agent::query()
            ->with('user')
            ->withCount(['transactions as period_txns' => fn ($q) => $q->whereBetween('transaction_date', [$from, $to])])
            ->withSum(['transactions as period_revenue' => fn ($q) => $q->whereBetween('transaction_date', [$from, $to])], 'amount_usd')
            ->withSum(['transactions as period_points' => fn ($q) => $q->whereBetween('transaction_date', [$from, $to])], 'points_awarded');

        $agents = match ($orderBy) {
            'txns'    => $agents->orderByDesc('period_txns'),
            'points'  => $agents->orderByDesc('period_points'),
            default   => $agents->orderByDesc('period_revenue'),
        };

        return [
            'range'  => compact('from', 'to'),
            'order_by' => $orderBy,
            'agents' => $agents->limit($limit)->get(),
        ];
    }

    /* ------------------------------------------------------------ */
    /* Helpers                                                      */
    /* ------------------------------------------------------------ */

    /**
     * Normalize range; defaults to last 30 days inclusive.
     *
     * @return array{0:string, 1:string} [from, to] as ISO datetimes
     */
    private function parseRange(array $range): array
    {
        $from = ! empty($range['from'])
            ? Carbon::parse($range['from'])->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();

        $to = ! empty($range['to'])
            ? Carbon::parse($range['to'])->endOfDay()
            : Carbon::now()->endOfDay();

        return [$from->toDateTimeString(), $to->toDateTimeString()];
    }

    /**
     * Fill missing days with zeros so the chart has a smooth x-axis.
     */
    private function fillDays($rows, string $from, string $to, array $fields): array
    {
        $indexed = collect($rows)->keyBy('day');
        $out     = [];
        $start   = Carbon::parse($from)->startOfDay();
        $end     = Carbon::parse($to)->startOfDay();

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->toDateString();
            $row = ['day' => $key];
            $r   = $indexed->get($key);
            foreach ($fields as $f) {
                $row[$f] = $r ? (float) $r->{$f} : 0;
            }
            $out[] = $row;
        }

        return $out;
    }
}
