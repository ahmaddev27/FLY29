<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentLevel;
use App\Models\TierHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Synchronous tier evaluation:
 * - Called after every new transaction.
 * - Counts packages in the evaluation window.
 * - If the agent qualifies for a HIGHER tier, immediately upgrades.
 * - If equal, refreshes tier_valid_until.
 * - Never auto-downgrades from this flow (downgrade is the Cron's job).
 */
class TierService
{
    public function __construct(private SettingsService $settings) {}

    /**
     * Order matters: [bronze, silver, gold, diamond]
     */
    private const TIER_ORDER = ['bronze', 'silver', 'gold', 'diamond'];

    /**
     * Evaluate the tier the agent currently qualifies for, based on packages
     * in the current evaluation window. Returns the qualifying tier name.
     */
    public function evaluateQualifyingTier(Agent $agent): string
    {
        $packagesInWindow = $this->countPackagesInWindow($agent);
        $levels           = AgentLevel::orderBy('min_packages_monthly', 'desc')->get();

        foreach ($levels as $level) {
            if ($packagesInWindow >= $level->min_packages_monthly) {
                return $level->tier_name;
            }
        }

        return 'bronze';
    }

    /**
     * After a transaction: check if the agent qualifies for upgrade and apply it.
     * Returns the new tier name if upgraded, null otherwise.
     */
    public function applyUpgradeIfQualified(Agent $agent): ?string
    {
        $qualifying = $this->evaluateQualifyingTier($agent);
        $current    = $agent->current_tier;

        $currentRank    = $this->tierRank($current);
        $qualifyingRank = $this->tierRank($qualifying);

        return DB::transaction(function () use ($agent, $current, $qualifying, $currentRank, $qualifyingRank) {
            if ($qualifyingRank > $currentRank) {
                $this->upgrade($agent, fromTier: $current, toTier: $qualifying);

                return $qualifying;
            }

            // same tier → renew the validity window
            $agent->update(['tier_valid_until' => now()->addDays(30)]);

            return null;
        });
    }

    /**
     * Force-apply a downgrade (called by EvaluateTiersCommand cron).
     */
    public function applyDowngrade(Agent $agent, string $toTier, ?int $packagesAtTime = null): void
    {
        DB::transaction(function () use ($agent, $toTier, $packagesAtTime) {
            $from = $agent->current_tier;
            $agent->update([
                'current_tier'     => $toTier,
                'tier_valid_until' => now()->addDays(30),
            ]);

            TierHistory::create([
                'agent_id'         => $agent->id,
                'from_tier'        => $from,
                'to_tier'          => $toTier,
                'action'           => 'downgrade',
                'packages_at_time' => $packagesAtTime ?? $this->countPackagesInWindow($agent),
                'valid_until'      => $agent->tier_valid_until,
                'triggered_by'     => 'system',
            ]);
        });
    }

    /**
     * Manual tier change by an admin (override).
     */
    public function applyManual(Agent $agent, string $toTier, int $adminId, ?string $notes = null): void
    {
        DB::transaction(function () use ($agent, $toTier, $adminId, $notes) {
            $from = $agent->current_tier;
            $agent->update([
                'current_tier'     => $toTier,
                'tier_valid_until' => now()->addDays(30),
            ]);

            TierHistory::create([
                'agent_id'         => $agent->id,
                'from_tier'        => $from,
                'to_tier'          => $toTier,
                'action'           => 'manual',
                'packages_at_time' => $this->countPackagesInWindow($agent),
                'valid_until'      => $agent->tier_valid_until,
                'triggered_by'     => 'admin',
                'admin_id'         => $adminId,
                'notes'            => $notes,
            ]);
        });
    }

    private function upgrade(Agent $agent, string $fromTier, string $toTier): void
    {
        $agent->update([
            'current_tier'     => $toTier,
            'tier_valid_until' => now()->addDays(30),
        ]);

        TierHistory::create([
            'agent_id'         => $agent->id,
            'from_tier'        => $fromTier,
            'to_tier'          => $toTier,
            'action'           => 'upgrade',
            'packages_at_time' => $this->countPackagesInWindow($agent),
            'valid_until'      => $agent->tier_valid_until,
            'triggered_by'     => 'system',
        ]);
    }

    /**
     * Count package transactions in the current evaluation window.
     * Mode controlled by `tier_evaluation_mode` setting:
     *   - calendar_month → from day 1 of current month
     *   - rolling_30_days → last 30 days from now
     */
    public function countPackagesInWindow(Agent $agent): int
    {
        $mode = (string) $this->settings->get('tier_evaluation_mode', 'calendar_month');

        $start = $mode === 'rolling_30_days'
            ? Carbon::now()->subDays(30)
            : Carbon::now()->startOfMonth();

        return $agent->transactions()
            ->where('transaction_type', 'package')
            ->where('transaction_date', '>=', $start)
            ->count();
    }

    private function tierRank(string $tier): int
    {
        $rank = array_search($tier, self::TIER_ORDER, true);

        return $rank === false ? 0 : (int) $rank;
    }
}
