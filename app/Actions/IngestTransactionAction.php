<?php

namespace App\Actions;

use App\Models\Agent;
use App\Models\PendingTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\IdempotencyService;
use App\Services\PointsCalculationService;
use App\Services\TierService;
use App\Services\WalletService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates a single webhook ingestion end-to-end.
 *
 * Steps:
 *   1. Check idempotency (reference_id unique).
 *   2. Resolve agent (external_agent_id → Agent). Handle missing/suspended.
 *   3. Inside a single DB transaction:
 *      a. Compute points + config snapshot.
 *      b. Insert `transactions` row.
 *      c. Credit both wallets via WalletService.
 *      d. Update agent's pending_amount (for amount_based fractions).
 *   4. After commit: evaluate tier upgrade (also DB-transactional).
 *   5. Return a result tuple for the controller.
 */
final class IngestTransactionAction
{
    public function __construct(
        private IdempotencyService $idempotency,
        private PointsCalculationService $pointsCalc,
        private WalletService $wallet,
        private TierService $tier,
    ) {}

    /**
     * @return array{
     *     status: 'accepted'|'duplicate_ignored'|'agent_not_found'|'agent_suspended',
     *     transaction?: Transaction,
     *     points_awarded?: int,
     *     new_balance?: array{cash:int, package:int},
     *     upgraded_to?: ?string,
     * }
     */
    public function execute(array $payload): array
    {
        // 1. Idempotency
        if ($existing = $this->idempotency->findExisting($payload['reference_id'])) {
            return [
                'status'       => 'duplicate_ignored',
                'transaction'  => $existing,
                'reference_id' => $existing->reference_id,
            ];
        }

        // 2. Resolve agent
        $agent = Agent::with('user')
            ->where('external_agent_id', $payload['agent_id'])
            ->first();

        if (! $agent) {
            return ['status' => 'agent_not_found'];
        }

        if ($agent->user && $agent->user->status === 'suspended') {
            // Hold for later processing
            PendingTransaction::updateOrCreate(
                ['reference_id' => $payload['reference_id']],
                [
                    'external_agent_id' => $payload['agent_id'],
                    'payload'           => $payload,
                    'reason'            => 'agent_suspended',
                    'processed'         => false,
                ]
            );

            return ['status' => 'agent_suspended'];
        }

        // 3. Atomic ingestion
        $result = DB::transaction(function () use ($agent, $payload) {
            $calc = $this->pointsCalc->calculate(
                $agent,
                $payload['transaction_type'],
                (float) $payload['amount_usd'],
            );

            $snapshot = $calc->toSnapshot();

            $transaction = Transaction::create([
                'agent_id'         => $agent->id,
                'reference_id'     => $payload['reference_id'],
                'transaction_type' => $payload['transaction_type'],
                'amount_usd'       => $payload['amount_usd'],
                'destination'      => $payload['destination'] ?? null,
                'points_awarded'   => $calc->points,
                'config_snapshot'  => $snapshot,
                'transaction_date' => Carbon::parse($payload['transaction_date']),
            ]);

            $balances = $this->wallet->credit(
                agent: $agent,
                wallet: WalletService::WALLET_BOTH,
                points: max(1, $calc->points), // service always grants ≥1; package may be 0 if amount too small
                source: 'transaction',
                sourceModel: $transaction,
                configSnapshot: $snapshot,
            );

            // Update pending_amount for amount_based mode
            if ($calc->calculationMethod === 'amount_based') {
                $agent->update(['pending_amount' => $calc->pendingAmountAccrued]);
            }

            return [
                'transaction' => $transaction,
                'points'      => $calc->points,
                'balances'    => $balances,
            ];
        });

        // 4. Tier evaluation (separate transaction)
        $upgradedTo = null;
        if ($payload['transaction_type'] === 'package') {
            $upgradedTo = $this->tier->applyUpgradeIfQualified($agent);
        }

        // 5. Format result
        return [
            'status'         => 'accepted',
            'transaction'    => $result['transaction'],
            'points_awarded' => $result['points'],
            'new_balance'    => [
                'cash'    => $result['balances']['cash'] ?? 0,
                'package' => $result['balances']['package'] ?? 0,
            ],
            'upgraded_to'    => $upgradedTo,
        ];
    }
}
