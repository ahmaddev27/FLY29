<?php

namespace App\Services;

use App\DTOs\PointsCalculationResult;
use App\Models\Agent;
use App\Models\AgentLevel;

class PointsCalculationService
{
    public function __construct(private SettingsService $settings) {}

    /**
     * Calculate points for a transaction based on current settings + agent tier.
     *
     * Service transactions always yield 1 point regardless of tier/method.
     * Package transactions follow the configured calculation method.
     */
    public function calculate(Agent $agent, string $transactionType, float $amountUsd): PointsCalculationResult
    {
        $method        = (string) $this->settings->get('calculation_method', 'package_based');
        $pointValueUsd = (float) $this->settings->get('point_value_usd', 2.0);
        $level         = AgentLevel::forTier($agent->current_tier);

        if (! $level) {
            throw new \RuntimeException("Unknown tier for agent #{$agent->id}: {$agent->current_tier}");
        }

        // Service: flat 1 point.
        if ($transactionType === 'service') {
            return new PointsCalculationResult(
                points: 1,
                calculationMethod: $method,
                tierAtTime: $agent->current_tier,
                pointValueUsdAtTime: $pointValueUsd,
            );
        }

        // Package transactions
        return $method === 'amount_based'
            ? $this->calculateAmountBased($agent, $amountUsd, $level, $pointValueUsd)
            : $this->calculatePackageBased($agent, $level, $pointValueUsd);
    }

    private function calculatePackageBased(Agent $agent, AgentLevel $level, float $pointValueUsd): PointsCalculationResult
    {
        return new PointsCalculationResult(
            points: $level->points_per_package,
            calculationMethod: 'package_based',
            tierAtTime: $agent->current_tier,
            pointValueUsdAtTime: $pointValueUsd,
            pointsPerPackageAtTime: $level->points_per_package,
        );
    }

    private function calculateAmountBased(
        Agent $agent,
        float $amountUsd,
        AgentLevel $level,
        float $pointValueUsd,
    ): PointsCalculationResult {
        $amountPerPoint = (float) $level->amount_per_point;
        $totalUsd       = $amountUsd + (float) $agent->pending_amount;

        $points         = (int) floor($totalUsd / $amountPerPoint);
        $consumedUsd    = $points * $amountPerPoint;
        $newPending     = max(0.0, $totalUsd - $consumedUsd);

        return new PointsCalculationResult(
            points: $points,
            calculationMethod: 'amount_based',
            tierAtTime: $agent->current_tier,
            pointValueUsdAtTime: $pointValueUsd,
            amountPerPointAtTime: $amountPerPoint,
            pendingAmountAccrued: $newPending,
        );
    }
}
