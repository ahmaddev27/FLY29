<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentLevel;
use App\Models\FreePackage;
use Illuminate\Support\Carbon;

/**
 * Aggregates everything an agent's dashboard needs in one shot.
 * Single source of truth — no controller should hit the DB directly.
 */
class AgentDashboardService
{
    public function __construct(
        private SettingsService $settings,
        private TierService $tiers,
    ) {}

    /**
     * @return array{
     *   agent: Agent,
     *   tier: array,
     *   wallets: array,
     *   kpis: array,
     *   recent_transactions: \Illuminate\Database\Eloquent\Collection,
     *   nearest_package: ?array,
     *   warnings: array<string>,
     * }
     */
    public function aggregate(Agent $agent): array
    {
        $agent->loadMissing(['cashWallet', 'packageWallet', 'tierLevel']);

        $packagesInWindow = $this->tiers->countPackagesInWindow($agent);
        $tierInfo         = $this->tierInfo($agent, $packagesInWindow);
        $pointValueUsd    = (float) $this->settings->get('point_value_usd', 2.0);

        return [
            'agent'               => $agent,
            'tier'                => $tierInfo,
            'wallets'             => $this->walletsInfo($agent, $pointValueUsd),
            'kpis'                => $this->kpis($agent, $packagesInWindow, $pointValueUsd),
            'recent_transactions' => $this->recentTransactions($agent),
            'nearest_package'     => $this->nearestRedeemablePackage($agent),
            'warnings'            => $this->warnings($agent, $tierInfo),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Tier
    |--------------------------------------------------------------------------
    */

    private function tierInfo(Agent $agent, int $packagesInWindow): array
    {
        $currentLevel = AgentLevel::forTier($agent->current_tier);
        $allLevels    = AgentLevel::orderBy('display_order')->get();
        $nextLevel    = $allLevels->where('display_order', '>', $currentLevel?->display_order ?? 0)->first();

        $threshold        = $nextLevel?->min_packages_monthly ?? $currentLevel?->min_packages_monthly ?? 0;
        $remainingPkgs    = $nextLevel ? max(0, $nextLevel->min_packages_monthly - $packagesInWindow) : 0;
        $progressPct      = $nextLevel && $nextLevel->min_packages_monthly > 0
            ? min(100, (int) round(($packagesInWindow / $nextLevel->min_packages_monthly) * 100))
            : 100;

        $daysUntilReeval  = $agent->tier_valid_until
            ? max(0, (int) Carbon::now()->diffInDays($agent->tier_valid_until, false))
            : 30;

        return [
            'current'              => $agent->current_tier,
            'current_label'        => $currentLevel?->benefits['label_ar'] ?? $agent->current_tier,
            'current_color'        => $currentLevel?->benefits['color']    ?? '#A16207',
            'points_per_package'   => $currentLevel?->points_per_package   ?? 2,
            'next_tier'            => $nextLevel?->tier_name,
            'next_tier_label'      => $nextLevel?->benefits['label_ar'],
            'packages_in_window'   => $packagesInWindow,
            'threshold_for_next'   => $threshold,
            'packages_remaining'   => $remainingPkgs,
            'progress_pct'         => $progressPct,
            'days_until_reeval'    => $daysUntilReeval,
            'valid_until'          => $agent->tier_valid_until,
            'is_max_tier'          => $nextLevel === null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Wallets
    |--------------------------------------------------------------------------
    */

    private function walletsInfo(Agent $agent, float $pointValueUsd): array
    {
        $cash    = $agent->cashWallet;
        $package = $agent->packageWallet;

        return [
            'cash' => [
                'available'         => (int) ($cash?->available_points ?? 0),
                'locked'            => (int) ($cash?->locked_points    ?? 0),
                'lifetime_earned'   => (int) ($cash?->lifetime_earned  ?? 0),
                'lifetime_redeemed' => (int) ($cash?->lifetime_redeemed ?? 0),
                'usd_value'         => round(((int) ($cash?->available_points ?? 0)) * $pointValueUsd, 2),
                'min_redemption'    => (int) $this->settings->get('min_redemption_points', 800),
                'can_redeem'        => (int) ($cash?->available_points ?? 0) >= (int) $this->settings->get('min_redemption_points', 800),
            ],
            'package' => [
                'available'         => (int) ($package?->available_points ?? 0),
                'locked'            => (int) ($package?->locked_points    ?? 0),
                'lifetime_earned'   => (int) ($package?->lifetime_earned  ?? 0),
                'lifetime_redeemed' => (int) ($package?->lifetime_redeemed ?? 0),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | KPIs (4 cards)
    |--------------------------------------------------------------------------
    */

    private function kpis(Agent $agent, int $packagesInWindow, float $pointValueUsd): array
    {
        $monthStart = Carbon::now()->startOfMonth();

        $pointsThisMonth = $agent->transactions()
            ->where('transaction_date', '>=', $monthStart)
            ->sum('points_awarded');

        $totalAvailable = (int) ($agent->cashWallet?->available_points ?? 0)
                       + (int) ($agent->packageWallet?->available_points ?? 0);

        return [
            'points_this_month'  => (int) $pointsThisMonth,
            'packages_this_month' => $packagesInWindow,
            'usd_value_total'    => round(((int) ($agent->cashWallet?->available_points ?? 0)) * $pointValueUsd, 2),
            'days_until_reeval'  => $agent->tier_valid_until
                ? max(0, (int) Carbon::now()->diffInDays($agent->tier_valid_until, false))
                : 30,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Recent transactions
    |--------------------------------------------------------------------------
    */

    private function recentTransactions(Agent $agent, int $limit = 10)
    {
        return $agent->transactions()
            ->orderByDesc('transaction_date')
            ->limit($limit)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Nearest package redeemable
    |--------------------------------------------------------------------------
    */

    private function nearestRedeemablePackage(Agent $agent): ?array
    {
        $balance  = (int) ($agent->packageWallet?->available_points ?? 0);
        $packages = FreePackage::active()
            ->orderBy('points_required')
            ->get();

        if ($packages->isEmpty()) {
            return null;
        }

        // closest package they could redeem now, otherwise the one nearest to their balance
        $affordable = $packages->where('points_required', '<=', $balance)->last();
        if ($affordable) {
            return [
                'package'        => $affordable,
                'can_redeem_now' => true,
                'points_needed'  => 0,
            ];
        }

        $next = $packages->where('points_required', '>', $balance)->first();
        if (! $next) {
            return null;
        }

        return [
            'package'        => $next,
            'can_redeem_now' => false,
            'points_needed'  => $next->points_required - $balance,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Warnings (banners)
    |--------------------------------------------------------------------------
    */

    private function warnings(Agent $agent, array $tierInfo): array
    {
        $warnings = [];

        // Tier downgrade risk: < 7 days left AND below current tier threshold
        $currentLevel = AgentLevel::forTier($agent->current_tier);
        $belowCurrent = $currentLevel && $tierInfo['packages_in_window'] < $currentLevel->min_packages_monthly;

        if ($tierInfo['days_until_reeval'] <= 7 && $belowCurrent && $agent->current_tier !== 'bronze') {
            $warnings[] = sprintf(
                'تنبيه: تصنيفك الحالي قد ينخفض خلال %d يوم — تحتاج %d باكج إضافي للحفاظ على %s.',
                $tierInfo['days_until_reeval'],
                $currentLevel->min_packages_monthly - $tierInfo['packages_in_window'],
                $tierInfo['current_label'],
            );
        }

        return $warnings;
    }
}
