<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\PendingAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Manual point adjustments (credit or debit) by admins.
 *
 * Workflow:
 *   - Small adjustments (|delta| ≤ dual_approval_threshold) are applied immediately.
 *   - Large adjustments enter the pending_adjustments queue and require a 2nd admin
 *     (super_admin) to approve before the wallet is touched.
 *
 * Returns either:
 *   ['applied' => true,  'balance' => N]
 *   ['applied' => false, 'pending' => PendingAdjustment]
 */
class AdjustmentService
{
    public function __construct(
        private WalletService $wallets,
        private SettingsService $settings,
        private AuditService $audit,
    ) {}

    /**
     * Request an adjustment. May apply immediately or queue for approval.
     */
    public function request(
        Agent $agent,
        string $wallet,
        int $delta,
        string $reason,
        User $requestedBy,
    ): array {
        if ($delta === 0) {
            throw new \InvalidArgumentException('Adjustment delta cannot be zero.');
        }

        $threshold = (int) $this->settings->get('dual_approval_threshold', 500);

        if (abs($delta) > $threshold) {
            return ['applied' => false, 'pending' => $this->queue($agent, $wallet, $delta, $reason, $requestedBy)];
        }

        return ['applied' => true, 'balance' => $this->apply($agent, $wallet, $delta, $reason, $requestedBy)];
    }

    /**
     * Approve a pending adjustment. Only super_admin can approve their own queue;
     * the original requester cannot self-approve.
     */
    public function approve(PendingAdjustment $adjustment, User $approver, ?string $notes = null): int
    {
        if ($adjustment->status !== 'pending') {
            throw new \DomainException("Cannot approve an adjustment in status: {$adjustment->status}");
        }
        if ($adjustment->requested_by === $approver->id) {
            throw new \DomainException('Cannot approve your own adjustment request.');
        }

        return DB::transaction(function () use ($adjustment, $approver, $notes) {
            $balance = $this->apply(
                $adjustment->agent,
                $adjustment->wallet_type,
                $adjustment->points_delta,
                $adjustment->reason,
                $approver,
                applyAsApproval: $adjustment,
            );

            $adjustment->update([
                'status'      => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'admin_notes' => $notes,
            ]);

            $this->audit->log(
                action: 'adjustment_approved',
                entityType: PendingAdjustment::class,
                entityId: (string) $adjustment->id,
                newValues: ['balance_after' => $balance],
            );

            return $balance;
        });
    }

    /**
     * Reject a pending adjustment. No wallet change.
     */
    public function reject(PendingAdjustment $adjustment, User $approver, string $notes): void
    {
        if ($adjustment->status !== 'pending') {
            throw new \DomainException("Cannot reject an adjustment in status: {$adjustment->status}");
        }

        $adjustment->update([
            'status'      => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);

        $this->audit->log(
            action: 'adjustment_rejected',
            entityType: PendingAdjustment::class,
            entityId: (string) $adjustment->id,
            newValues: ['reason' => $notes],
        );
    }

    /**
     * Cancel a pending request — only the requester can cancel.
     */
    public function cancel(PendingAdjustment $adjustment, User $by): void
    {
        if ($adjustment->status !== 'pending') {
            throw new \DomainException('Only pending adjustments can be cancelled.');
        }
        if ($adjustment->requested_by !== $by->id) {
            throw new \DomainException('Only the original requester can cancel this adjustment.');
        }

        $adjustment->update(['status' => 'cancelled']);

        $this->audit->log(
            action: 'adjustment_cancelled',
            entityType: PendingAdjustment::class,
            entityId: (string) $adjustment->id,
        );
    }

    /* -------------------------------------------------------------------- */
    /* Internals                                                            */
    /* -------------------------------------------------------------------- */

    /**
     * Apply the wallet movement. Used both for direct adjustments and for
     * approval-completed adjustments.
     */
    private function apply(
        Agent $agent,
        string $wallet,
        int $delta,
        string $reason,
        User $actor,
        ?PendingAdjustment $applyAsApproval = null,
    ): int {
        $description = $applyAsApproval
            ? "تعديل يدوي (موافقة على #{$applyAsApproval->id}): {$reason}"
            : "تعديل يدوي: {$reason}";

        if ($delta > 0) {
            $balances = $this->wallets->credit(
                agent: $agent,
                wallet: $wallet,
                points: $delta,
                source: 'manual_adjustment',
                createdBy: $actor->id,
                description: $description,
            );
            $balance = $balances[$wallet];
        } else {
            $balance = $this->wallets->debit(
                agent: $agent,
                wallet: $wallet,
                points: abs($delta),
                source: 'manual_adjustment',
                createdBy: $actor->id,
                description: $description,
            );
        }

        if (! $applyAsApproval) {
            // Direct adjustment audit (queue-then-approve audits separately)
            $this->audit->log(
                action: 'adjustment_applied',
                entityType: Agent::class,
                entityId: (string) $agent->id,
                newValues: [
                    'wallet'        => $wallet,
                    'delta'         => $delta,
                    'reason'        => $reason,
                    'balance_after' => $balance,
                ],
            );
        }

        return $balance;
    }

    private function queue(
        Agent $agent,
        string $wallet,
        int $delta,
        string $reason,
        User $requestedBy,
    ): PendingAdjustment {
        $adjustment = PendingAdjustment::create([
            'agent_id'     => $agent->id,
            'wallet_type'  => $wallet,
            'points_delta' => $delta,
            'reason'       => $reason,
            'requested_by' => $requestedBy->id,
            'status'       => 'pending',
        ]);

        $this->audit->log(
            action: 'adjustment_queued',
            entityType: PendingAdjustment::class,
            entityId: (string) $adjustment->id,
            newValues: $adjustment->getAttributes(),
        );

        return $adjustment;
    }
}
