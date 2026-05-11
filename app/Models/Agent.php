<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'external_agent_id',
        'business_name',
        'license_number',
        'country',
        'city',
        'current_tier',
        'tier_valid_until',
        'account_manager_id',
        'pending_amount',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'tier_valid_until' => 'datetime',
            'pending_amount'   => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function cashWallet(): HasOne
    {
        return $this->hasOne(CashWalletPoints::class);
    }

    public function packageWallet(): HasOne
    {
        return $this->hasOne(PackageWalletPoints::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(RedemptionRequest::class);
    }

    public function pointsHistory(): HasMany
    {
        return $this->hasMany(PointsHistory::class);
    }

    public function tierHistory(): HasMany
    {
        return $this->hasMany(TierHistory::class);
    }

    public function pendingAdjustments(): HasMany
    {
        return $this->hasMany(PendingAdjustment::class);
    }

    public function tierLevel(): BelongsTo
    {
        return $this->belongsTo(AgentLevel::class, 'current_tier', 'tier_name');
    }
}
