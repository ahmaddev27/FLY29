<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingAdjustment extends Model
{
    protected $fillable = [
        'agent_id',
        'wallet_type',
        'points_delta',
        'reason',
        'requested_by',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'points_delta' => 'integer',
            'approved_at'  => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
