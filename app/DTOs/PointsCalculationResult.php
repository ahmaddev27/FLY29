<?php

namespace App\DTOs;

/**
 * Result of points calculation for a single transaction.
 * Immutable — calculated once per webhook ingestion.
 */
final readonly class PointsCalculationResult
{
    public function __construct(
        public int $points,
        public string $calculationMethod,    // package_based | amount_based
        public string $tierAtTime,
        public int|float $pointValueUsdAtTime,
        public ?int $pointsPerPackageAtTime = null,
        public ?float $amountPerPointAtTime = null,
        public float $pendingAmountAccrued = 0.0, // for amount_based fractions
    ) {}

    public function toSnapshot(): array
    {
        return array_filter([
            'calculation_method'            => $this->calculationMethod,
            'tier_at_time'                  => $this->tierAtTime,
            'point_value_usd_at_time'       => $this->pointValueUsdAtTime,
            'points_per_package_at_time'    => $this->pointsPerPackageAtTime,
            'amount_per_point_at_time'      => $this->amountPerPointAtTime,
            'pending_amount_accrued'        => $this->pendingAmountAccrued,
        ], fn ($v) => $v !== null);
    }
}
