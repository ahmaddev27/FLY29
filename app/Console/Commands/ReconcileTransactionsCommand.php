<?php

namespace App\Console\Commands;

use App\Models\ApiLog;
use App\Models\Transaction;
use App\Services\MainSiteApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Daily reconciliation between the loyalty DB and the Main Site (fly29.net).
 *
 * For the target date:
 *   1. Fetch the daily summary from Main Site (count + total amount).
 *   2. Compare against our own totals from `transactions`.
 *   3. If the numbers diverge → flag a discrepancy, log it, and (in the
 *      next sprint) optionally pull the full list to identify missing
 *      reference_ids.
 *
 * Outcomes are surfaced both via console output and as an ApiLog row so
 * the admin can review them in /admin/api-logs.
 */
class ReconcileTransactionsCommand extends Command
{
    protected $signature   = 'transactions:reconcile {date? : YYYY-MM-DD — defaults to yesterday}';
    protected $description = 'Reconcile daily transactions with the Main Site and flag discrepancies.';

    public function handle(MainSiteApiService $api): int
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'))->toDateString()
            : Carbon::yesterday()->toDateString();

        $this->info("Reconciling transactions for {$date}...");

        // 1) Local totals
        $local = Transaction::whereDate('transaction_date', $date)
            ->selectRaw('COUNT(*) as c, COALESCE(SUM(amount_usd), 0) as total')
            ->first();

        $localCount  = (int) ($local->c ?? 0);
        $localTotal  = (float) ($local->total ?? 0);

        // 2) Remote totals via Main Site API
        try {
            $remote = $api->dailySummary($date);
        } catch (Throwable $e) {
            $this->error("Failed to fetch Main Site summary: {$e->getMessage()}");
            $this->logResult($date, $localCount, $localTotal, null, null, 'api_error', $e->getMessage());

            return self::FAILURE;
        }

        $remoteCount = (int) ($remote['count'] ?? 0);
        $remoteTotal = (float) ($remote['total_amount_usd'] ?? 0);

        $countDiff   = $remoteCount - $localCount;
        $amountDiff  = round($remoteTotal - $localTotal, 2);
        $hasMismatch = $countDiff !== 0 || abs($amountDiff) > 0.01;

        // 3) Output + log
        $this->table(['', 'Local', 'Main Site', 'Diff'], [
            ['count',  $localCount,                       $remoteCount,                       $countDiff],
            ['amount', '$' . number_format($localTotal, 2), '$' . number_format($remoteTotal, 2), '$' . number_format($amountDiff, 2)],
        ]);

        if ($hasMismatch) {
            $this->warn("⚠ Discrepancy detected for {$date}.");
        } else {
            $this->info("✓ Numbers match for {$date}.");
        }

        $this->logResult(
            $date, $localCount, $localTotal, $remoteCount, $remoteTotal,
            $hasMismatch ? 'discrepancy' : 'ok',
            null
        );

        return self::SUCCESS;
    }

    private function logResult(
        string $date,
        int $localCount,
        float $localTotal,
        ?int $remoteCount,
        ?float $remoteTotal,
        string $status,
        ?string $error,
    ): void {
        ApiLog::create([
            'method'        => 'CRON',
            'endpoint'      => 'transactions:reconcile',
            'response_code' => $status === 'ok' ? 200 : 422,
            'response_body' => [
                'date'         => $date,
                'local_count'  => $localCount,
                'local_total'  => $localTotal,
                'remote_count' => $remoteCount,
                'remote_total' => $remoteTotal,
                'status'       => $status,
                'error'        => $error,
            ],
            'status'        => $status === 'ok' ? 'success' : 'failed',
        ]);
    }
}
