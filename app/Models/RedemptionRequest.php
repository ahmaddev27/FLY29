<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedemptionRequest extends Model
{
    protected $fillable = [
        'agent_id',
        'type',
        'points',
        'cash_value_usd',
        'package_id',
        'status',
        'rejection_reason',
        'fulfilled',
        'requested_at',
        'processed_at',
        'processed_by',
        'fulfilled_at',
        'fulfilled_by',
        'fulfillment_reference',
        'fulfillment_notes',
    ];

    protected function casts(): array
    {
        return [
            'points'         => 'integer',
            'cash_value_usd' => 'decimal:2',
            'fulfilled'      => 'boolean',
            'requested_at'   => 'datetime',
            'processed_at'   => 'datetime',
            'fulfilled_at'   => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(FreePackage::class, 'package_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function fulfiller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function pointsHistory(): HasMany
    {
        return $this->hasMany(PointsHistory::class, 'redemption_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
