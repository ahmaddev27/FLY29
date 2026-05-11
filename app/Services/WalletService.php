<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\CashWalletPoints;
use App\Models\PackageWalletPoints;
use App\Models\PointsHistory;
use App\Models\RedemptionRequest;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Atomic operations on the two independent wallets (cash + package).
 *
 * Every public method runs inside a DB transaction with pessimistic row locking
 * to prevent race conditions on the wallet balance.
 *
 * `$wallet` arg is either 'cash' or 'package' (or 'both' for credit).
 */
class WalletService
{
    public const WALLET_CASH    = 'cash';
    public const WALLET_PACKAGE = 'package';
    public const WALLET_BOTH    = 'both';

    /**
     * Credit points to the wallet(s).
     * For 'both', adds the same amount to both wallets (typical for new transactions).
     */
    public function credit(
        Agent $agent,
        string $wallet,
        int $points,
        string $source,
        ?Model $sourceModel = null,
        ?array $configSnapshot = null,
        ?int $createdBy = null,
        ?string $description = null,
    ): array {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Credit points must be positive.');
        }

        return DB::transaction(function () use ($agent, $wallet, $points, $source, $sourceModel, $configSnapshot, $createdBy, $description) {
            $balances = [];

            foreach ($this->resolveWallets($wallet) as $type) {
                $row = $this->lockWalletRow($agent, $type);
                $row->available_points += $points;
                $row->lifetime_earned  += $points;
                $row->save();

                $this->recordHistory(
                    agent: $agent,
                    walletType: $type,
                    pointsDelta: $points,
                    balanceAfter: $row->available_points,
                    source: $source,
                    sourceModel: $sourceModel,
                    configSnapshot: $configSnapshot,
                    createdBy: $createdBy,
                    description: $description,
                );

                $balances[$type] = $row->available_points;
            }

            return $balances;
        });
    }

    /**
     * Debit points (final deduction) from a single wallet.
     */
    public function debit(
        Agent $agent,
        string $wallet,
        int $points,
        string $source,
        ?Model $sourceModel = null,
        ?int $createdBy = null,
        ?string $description = null,
    ): int {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Debit points must be positive.');
        }
        if ($wallet === self::WALLET_BOTH) {
            throw new \InvalidArgumentException('Cannot debit from BOTH wallets at once.');
        }

        return DB::transaction(function () use ($agent, $wallet, $points, $source, $sourceModel, $createdBy, $description) {
            $row = $this->lockWalletRow($agent, $wallet);

            if ($row->available_points < $points) {
                throw new \DomainException("Insufficient {$wallet} balance: have {$row->available_points}, need {$points}.");
            }

            $row->available_points  -= $points;
            $row->lifetime_redeemed += $points;
            $row->save();

            $this->recordHistory(
                agent: $agent,
                walletType: $wallet,
                pointsDelta: -$points,
                balanceAfter: $row->available_points,
                source: $source,
                sourceModel: $sourceModel,
                createdBy: $createdBy,
                description: $description,
            );

            return $row->available_points;
        });
    }

    /**
     * Move points from available to locked (e.g. on redemption request submit).
     */
    public function lockPoints(Agent $agent, string $wallet, int $points): int
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Lock points must be positive.');
        }

        return DB::transaction(function () use ($agent, $wallet, $points) {
            $row = $this->lockWalletRow($agent, $wallet);

            if ($row->available_points < $points) {
                throw new \DomainException("Cannot lock {$points}: only {$row->available_points} available.");
            }

            $row->available_points -= $points;
            $row->locked_points    += $points;
            $row->save();

            return $row->available_points;
        });
    }

    /**
     * Return locked points to available (e.g. on rejection or cancellation).
     */
    public function unlockPoints(Agent $agent, string $wallet, int $points): int
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Unlock points must be positive.');
        }

        return DB::transaction(function () use ($agent, $wallet, $points) {
            $row = $this->lockWalletRow($agent, $wallet);

            if ($row->locked_points < $points) {
                throw new \DomainException("Cannot unlock {$points}: only {$row->locked_points} locked.");
            }

            $row->locked_points    -= $points;
            $row->available_points += $points;
            $row->save();

            return $row->available_points;
        });
    }

    /**
     * Final deduction from locked (e.g. on approval — moves from locked to "spent").
     */
    public function finalizeLocked(Agent $agent, string $wallet, int $points): int
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Points must be positive.');
        }

        return DB::transaction(function () use ($agent, $wallet, $points) {
            $row = $this->lockWalletRow($agent, $wallet);

            if ($row->locked_points < $points) {
                throw new \DomainException("Cannot finalize {$points}: only {$row->locked_points} locked.");
            }

            $row->locked_points    -= $points;
            $row->lifetime_redeemed += $points;
            $row->save();

            return $row->locked_points;
        });
    }

    /**
     * Lock the wallet row for update (pessimistic lock).
     * Creates a row if missing (defensive — should always exist for agents).
     */
    private function lockWalletRow(Agent $agent, string $wallet): CashWalletPoints|PackageWalletPoints
    {
        $model = $wallet === self::WALLET_CASH ? CashWalletPoints::class : PackageWalletPoints::class;

        return $model::where('agent_id', $agent->id)->lockForUpdate()->first()
            ?? $model::create(['agent_id' => $agent->id]);
    }

    private function resolveWallets(string $wallet): array
    {
        return match ($wallet) {
            self::WALLET_CASH    => [self::WALLET_CASH],
            self::WALLET_PACKAGE => [self::WALLET_PACKAGE],
            self::WALLET_BOTH    => [self::WALLET_CASH, self::WALLET_PACKAGE],
            default => throw new \InvalidArgumentException("Unknown wallet: {$wallet}"),
        };
    }

    private function recordHistory(
        Agent $agent,
        string $walletType,
        int $pointsDelta,
        int $balanceAfter,
        string $source,
        ?Model $sourceModel,
        ?array $configSnapshot = null,
        ?int $createdBy = null,
        ?string $description = null,
    ): void {
        $payload = [
            'agent_id'        => $agent->id,
            'wallet_type'     => $walletType,
            'points_delta'    => $pointsDelta,
            'balance_after'   => $balanceAfter,
            'source'          => $source,
            'config_snapshot' => $configSnapshot,
            'created_by'      => $createdBy,
            'description'     => $description,
        ];

        if ($sourceModel instanceof Transaction) {
            $payload['transaction_id'] = $sourceModel->id;
        } elseif ($sourceModel instanceof RedemptionRequest) {
            $payload['redemption_id'] = $sourceModel->id;
        }

        PointsHistory::create($payload);
    }
}
