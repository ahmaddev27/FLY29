<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\FreePackage;
use App\Models\PointsHistory;
use App\Models\RedemptionRequest;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * High-level redemption flows.
 *
 * - createCash: agent submits a cash request → points moved from available → locked
 * - approveCash: admin approves → locked points permanently deducted, lifetime_redeemed bumped
 * - rejectCash: admin rejects → locked points returned to available
 * - cancel: agent cancels their own pending request → locked returned to available
 * - redeemPackage: instant flow, points deducted immediately (auto-approved logistically)
 */
class RedemptionService
{
    public function __construct(
        private WalletService $wallet,
        private SettingsService $settings,
        private AuditService $audit,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Cash redemption flow
    |--------------------------------------------------------------------------
    */

    public function createCashRequest(Agent $agent, int $points): RedemptionRequest
    {
        $min = (int) $this->settings->get('min_redemption_points', 800);

        if ($points < $min) {
            throw new DomainException("الحد الأدنى للتحويل {$min} نقطة.");
        }

        $pointValue = (float) $this->settings->get('point_value_usd', 2.0);

        return DB::transaction(function () use ($agent, $points, $pointValue) {
            // Lock points (re-checks available balance internally)
            $this->wallet->lockPoints($agent, WalletService::WALLET_CASH, $points);

            $request = RedemptionRequest::create([
                'agent_id'       => $agent->id,
                'type'           => 'cash',
                'points'         => $points,
                'cash_value_usd' => round($points * $pointValue, 2),
                'status'         => 'pending',
                'requested_at'   => now(),
            ]);

            $this->audit->log(
                action: 'cash_redemption_requested',
                entityType: RedemptionRequest::class,
                entityId: (string) $request->id,
                newValues: ['points' => $points, 'usd' => $request->cash_value_usd],
                userId: $agent->user_id,
            );

            return $request;
        });
    }

    public function approveCash(RedemptionRequest $request, User $admin): RedemptionRequest
    {
        if ($request->type !== 'cash' || $request->status !== 'pending') {
            throw new DomainException('الطلب غير قابل للموافقة في حالته الحالية.');
        }

        return DB::transaction(function () use ($request, $admin) {
            $agent = $request->agent;

            // Moves from locked to spent (lifetime_redeemed++) — NOT back to available
            $this->wallet->finalizeLocked($agent, WalletService::WALLET_CASH, $request->points);

            $request->update([
                'status'       => 'approved',
                'processed_at' => now(),
                'processed_by' => $admin->id,
            ]);

            // Record in points_history (for the user-facing log)
            PointsHistory::create([
                'agent_id'      => $agent->id,
                'wallet_type'   => 'cash',
                'redemption_id' => $request->id,
                'points_delta'  => -$request->points,
                'balance_after' => $agent->cashWallet->fresh()->available_points,
                'source'        => 'redemption',
                'description'   => "تحويل نقدي مُعتمد بقيمة \${$request->cash_value_usd}",
                'created_by'    => $admin->id,
            ]);

            $this->audit->log(
                action: 'cash_redemption_approved',
                entityType: RedemptionRequest::class,
                entityId: (string) $request->id,
                newValues: ['admin_id' => $admin->id, 'points' => $request->points],
                userId: $admin->id,
            );

            return $request->fresh();
        });
    }

    public function rejectCash(RedemptionRequest $request, User $admin, string $reason): RedemptionRequest
    {
        if ($request->type !== 'cash' || $request->status !== 'pending') {
            throw new DomainException('الطلب غير قابل للرفض في حالته الحالية.');
        }

        return DB::transaction(function () use ($request, $admin, $reason) {
            $agent = $request->agent;

            // Unlock points (locked → available)
            $this->wallet->unlockPoints($agent, WalletService::WALLET_CASH, $request->points);

            $request->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'processed_at'     => now(),
                'processed_by'     => $admin->id,
            ]);

            PointsHistory::create([
                'agent_id'      => $agent->id,
                'wallet_type'   => 'cash',
                'redemption_id' => $request->id,
                'points_delta'  => $request->points, // positive — refund
                'balance_after' => $agent->cashWallet->fresh()->available_points,
                'source'        => 'rejection_refund',
                'description'   => "استرداد نقاط: {$reason}",
                'created_by'    => $admin->id,
            ]);

            $this->audit->log(
                action: 'cash_redemption_rejected',
                entityType: RedemptionRequest::class,
                entityId: (string) $request->id,
                newValues: ['admin_id' => $admin->id, 'reason' => $reason],
                userId: $admin->id,
            );

            return $request->fresh();
        });
    }

    public function cancel(RedemptionRequest $request, User $agentUser): RedemptionRequest
    {
        if ($request->status !== 'pending') {
            throw new DomainException('لا يمكن إلغاء طلب غير معلّق.');
        }

        if ($request->agent->user_id !== $agentUser->id) {
            throw new DomainException('غير مصرّح بإلغاء طلب لوكيل آخر.');
        }

        return DB::transaction(function () use ($request, $agentUser) {
            $agent  = $request->agent;
            $wallet = $request->type === 'cash'
                ? WalletService::WALLET_CASH
                : WalletService::WALLET_PACKAGE;

            // For package: points were already debited (auto-approved). Refund them.
            // For cash: points are locked. Unlock them.
            if ($request->type === 'cash') {
                $this->wallet->unlockPoints($agent, $wallet, $request->points);
            } else {
                // Package: re-credit (rare, since packages are usually instantly fulfilled)
                $this->wallet->credit(
                    agent: $agent,
                    wallet: $wallet,
                    points: $request->points,
                    source: 'cancellation_refund',
                    sourceModel: $request,
                    createdBy: $agentUser->id,
                    description: 'استرداد نقاط بعد إلغاء طلب الباكج',
                );
            }

            $request->update([
                'status'       => 'cancelled',
                'processed_at' => now(),
            ]);

            $this->audit->log(
                action: 'redemption_cancelled',
                entityType: RedemptionRequest::class,
                entityId: (string) $request->id,
                userId: $agentUser->id,
            );

            return $request->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Package redemption (instant)
    |--------------------------------------------------------------------------
    */

    public function redeemPackage(Agent $agent, FreePackage $package): RedemptionRequest
    {
        if (! $package->is_active) {
            throw new DomainException('هذا الباكج غير متاح حالياً.');
        }

        if ($package->valid_until && $package->valid_until->isPast()) {
            throw new DomainException('انتهت صلاحية هذا الباكج.');
        }

        return DB::transaction(function () use ($agent, $package) {
            // Permanent debit from package_wallet
            $this->wallet->debit(
                agent: $agent,
                wallet: WalletService::WALLET_PACKAGE,
                points: $package->points_required,
                source: 'redemption',
                createdBy: $agent->user_id,
                description: "استبدال باكج: {$package->name}",
            );

            $request = RedemptionRequest::create([
                'agent_id'     => $agent->id,
                'type'         => 'package',
                'points'       => $package->points_required,
                'package_id'   => $package->id,
                'status'       => 'approved', // instant approval
                'fulfilled'    => false,      // logistics pending
                'requested_at' => now(),
                'processed_at' => now(),
            ]);

            $this->audit->log(
                action: 'package_redeemed',
                entityType: RedemptionRequest::class,
                entityId: (string) $request->id,
                newValues: [
                    'package_id'   => $package->id,
                    'package_name' => $package->name,
                    'points'       => $package->points_required,
                ],
                userId: $agent->user_id,
            );

            return $request;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfillment — marks an approved request as actually paid/booked
    |--------------------------------------------------------------------------
    | Cash:    after the bank transfer was sent → reference = transfer ref
    | Package: after the trip was booked       → reference = booking number
    */

    public function fulfill(
        RedemptionRequest $request,
        User $by,
        ?string $reference = null,
        ?string $notes = null,
    ): RedemptionRequest {
        if ($request->status !== 'approved') {
            throw new DomainException('فقط الطلبات المعتمدة يمكن تنفيذها (fulfilled).');
        }

        if ($request->fulfilled) {
            throw new DomainException('هذا الطلب تم تنفيذه مسبقاً.');
        }

        $request->update([
            'status'                => 'fulfilled',
            'fulfilled'             => true,
            'fulfilled_at'          => now(),
            'fulfilled_by'          => $by->id,
            'fulfillment_reference' => $reference,
            'fulfillment_notes'     => $notes,
        ]);

        $this->audit->log(
            action: 'redemption_fulfilled',
            entityType: RedemptionRequest::class,
            entityId: (string) $request->id,
            newValues: [
                'type'      => $request->type,
                'reference' => $reference,
                'notes'     => $notes,
            ],
        );

        return $request->fresh();
    }

    /**
     * Reverse a fulfillment (e.g. payment failed at the bank and needs reissue).
     * The redemption goes back to 'approved' and the fulfillment fields are cleared.
     * Wallet balances are NOT touched — points stay deducted.
     */
    public function reverseFulfillment(RedemptionRequest $request, User $by, string $reason): RedemptionRequest
    {
        if ($request->status !== 'fulfilled' || ! $request->fulfilled) {
            throw new DomainException('فقط الطلبات المنفّذة يمكن عكس تنفيذها.');
        }

        $oldRef = $request->fulfillment_reference;

        $request->update([
            'status'                => 'approved',
            'fulfilled'             => false,
            'fulfilled_at'          => null,
            'fulfilled_by'          => null,
            'fulfillment_reference' => null,
            'fulfillment_notes'     => null,
        ]);

        $this->audit->log(
            action: 'redemption_fulfillment_reversed',
            entityType: RedemptionRequest::class,
            entityId: (string) $request->id,
            oldValues: ['fulfillment_reference' => $oldRef],
            newValues: ['reason' => $reason, 'reversed_by' => $by->id],
        );

        return $request->fresh();
    }
}
