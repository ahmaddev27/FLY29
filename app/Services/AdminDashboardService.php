<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\RedemptionRequest;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Aggregates everything the admin dashboard needs in one pass.
 * Returns a single payload — controllers should never touch the DB.
 */
class AdminDashboardService
{
    public function aggregate(): array
    {
        return [
            'kpis'           => $this->kpis(),
            'tier_breakdown' => $this->tierBreakdown(),
            'top_agents'     => $this->topAgents(),
            'pending_count'  => $this->pendingRequestsCount(),
            'recent_pending' => $this->recentPending(),
            'charts'         => [
                'sales_growth'  => $this->salesGrowth(12),
                'agent_growth'  => $this->agentGrowth(12),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Top-line KPIs
    |--------------------------------------------------------------------------
    */
    private function kpis(): array
    {
        $totalAgents     = Agent::count();
        $activeAgents    = Agent::whereHas('user', fn ($q) => $q->where('status', 'active'))->count();

        $monthStart      = Carbon::now()->startOfMonth();
        $monthlyTxns     = Transaction::where('transaction_date', '>=', $monthStart)->count();
        $monthlyRevenue  = (float) Transaction::where('transaction_date', '>=', $monthStart)->sum('amount_usd');
        $monthlyPoints   = (int)   Transaction::where('transaction_date', '>=', $monthStart)->sum('points_awarded');

        $lifetimeRedeemed = (int) DB::table('cash_wallet_points')->sum('lifetime_redeemed')
                          + (int) DB::table('package_wallet_points')->sum('lifetime_redeemed');
        $lifetimeEarned   = (int) DB::table('cash_wallet_points')->sum('lifetime_earned')
                          + (int) DB::table('package_wallet_points')->sum('lifetime_earned');

        return [
            'total_agents'      => $totalAgents,
            'active_agents'     => $activeAgents,
            'monthly_txns'      => $monthlyTxns,
            'monthly_revenue'   => round($monthlyRevenue, 2),
            'monthly_points'    => $monthlyPoints,
            'lifetime_earned'   => $lifetimeEarned,
            'lifetime_redeemed' => $lifetimeRedeemed,
            'liability_points'  => max(0, $lifetimeEarned - $lifetimeRedeemed),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Tier distribution
    |--------------------------------------------------------------------------
    */
    private function tierBreakdown(): array
    {
        $rows = Agent::query()
            ->select('current_tier', DB::raw('COUNT(*) as count'))
            ->groupBy('current_tier')
            ->pluck('count', 'current_tier')
            ->toArray();

        // ensure all 4 tiers appear
        return [
            'bronze'  => (int) ($rows['bronze']  ?? 0),
            'silver'  => (int) ($rows['silver']  ?? 0),
            'gold'    => (int) ($rows['gold']    ?? 0),
            'diamond' => (int) ($rows['diamond'] ?? 0),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Top 10 agents this month
    |--------------------------------------------------------------------------
    */
    private function topAgents(int $limit = 10): Collection
    {
        $monthStart = Carbon::now()->startOfMonth();

        return Agent::query()
            ->with('user')
            ->withCount(['transactions as monthly_packages' => fn ($q) => $q
                ->where('transaction_date', '>=', $monthStart)
                ->where('transaction_type', 'package')])
            ->withSum(['transactions as monthly_revenue' => fn ($q) => $q
                ->where('transaction_date', '>=', $monthStart)], 'amount_usd')
            ->withSum(['transactions as monthly_points' => fn ($q) => $q
                ->where('transaction_date', '>=', $monthStart)], 'points_awarded')
            ->orderByDesc('monthly_revenue')
            ->limit($limit)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Pending redemption requests
    |--------------------------------------------------------------------------
    */
    private function pendingRequestsCount(): int
    {
        return RedemptionRequest::where('status', 'pending')->count();
    }

    private function recentPending(int $limit = 5): Collection
    {
        return RedemptionRequest::with('agent')
            ->where('status', 'pending')
            ->latest('requested_at')
            ->limit($limit)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Charts (data points per month for the last N months)
    |--------------------------------------------------------------------------
    */
    private function salesGrowth(int $months): array
    {
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $rows = Transaction::query()
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as bucket"),
                DB::raw('COUNT(*) as txn_count'),
                DB::raw('SUM(amount_usd) as revenue'),
            )
            ->where('transaction_date', '>=', $start)
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        return $this->fillMonths($start, $months, function (string $bucket) use ($rows) {
            return [
                'txn_count' => (int)   ($rows[$bucket]->txn_count ?? 0),
                'revenue'   => (float) ($rows[$bucket]->revenue   ?? 0),
            ];
        });
    }

    private function agentGrowth(int $months): array
    {
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $rows = Agent::query()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as bucket"),
                DB::raw('COUNT(*) as new_agents'),
            )
            ->where('created_at', '>=', $start)
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        return $this->fillMonths($start, $months, function (string $bucket) use ($rows) {
            return ['new_agents' => (int) ($rows[$bucket]->new_agents ?? 0)];
        });
    }

    /**
     * Fill in zero values for months with no data so the chart is smooth.
     */
    private function fillMonths(Carbon $start, int $count, callable $valueFor): array
    {
        $out = [];
        $cursor = $start->copy();
        for ($i = 0; $i < $count; $i++) {
            $bucket = $cursor->format('Y-m');
            $out[] = array_merge(['label' => $cursor->isoFormat('MMM YY')], $valueFor($bucket));
            $cursor->addMonth();
        }

        return $out;
    }
}
