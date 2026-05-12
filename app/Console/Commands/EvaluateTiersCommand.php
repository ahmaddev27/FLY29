<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Services\TierService;
use Illuminate\Console\Command;

/**
 * Daily tier-evaluation pass.
 *
 * For every active agent whose tier_valid_until has passed:
 *  - Recompute the qualifying tier from packages in the current window.
 *  - If they no longer qualify for their current tier → downgrade.
 *  - If they still qualify or qualify higher → refresh tier_valid_until.
 *
 * Upgrades happen synchronously on every transaction (see TierService::
 * applyUpgradeIfQualified), so this cron only needs to handle downgrades
 * and renewals.
 */
class EvaluateTiersCommand extends Command
{
    protected $signature   = 'tiers:evaluate {--dry-run : Show what would change without writing}';
    protected $description = 'Re-evaluate every active agent\'s tier and apply downgrades for expired tiers.';

    public function handle(TierService $tiers): int
    {
        $dry = $this->option('dry-run');
        $now = now();

        $query = Agent::query()
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->where(function ($q) use ($now) {
                $q->whereNull('tier_valid_until')->orWhere('tier_valid_until', '<=', $now);
            });

        $total = $query->count();
        $this->info("Evaluating {$total} agent(s) with expired tier validity...");

        $downgraded = 0;
        $renewed    = 0;

        $query->chunkById(200, function ($agents) use ($tiers, $dry, &$downgraded, &$renewed) {
            foreach ($agents as $agent) {
                $qualifying = $tiers->evaluateQualifyingTier($agent);

                $currentRank    = array_search($agent->current_tier, ['bronze', 'silver', 'gold', 'diamond'], true);
                $qualifyingRank = array_search($qualifying, ['bronze', 'silver', 'gold', 'diamond'], true);

                if ($qualifyingRank < $currentRank) {
                    if (! $dry) {
                        $tiers->applyDowngrade($agent, $qualifying);
                    }
                    $downgraded++;
                    $this->line("  ↓ {$agent->business_name}: {$agent->current_tier} → {$qualifying}");
                } else {
                    if (! $dry) {
                        $agent->update(['tier_valid_until' => now()->addDays(30)]);
                    }
                    $renewed++;
                }
            }
        });

        $verb = $dry ? '(dry-run) would' : 'has';
        $this->info("Tier evaluation {$verb} downgraded {$downgraded} and renewed {$renewed}.");

        return self::SUCCESS;
    }
}
