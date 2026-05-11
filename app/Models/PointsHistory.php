<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsHistory extends Model
{
    protected $table = 'points_history';

    public $timestamps = false; // Has only created_at

    protected $fillable = [
        'agent_id',
        'wallet_type',
        'transaction_id',
        'redemption_id',
        'points_delta',
        'balance_after',
        'source',
        'description',
        'config_snapshot',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'points_delta'    => 'integer',
            'balance_after'   => 'integer',
            'config_snapshot' => 'array',
            'created_at'      => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function redemption(): BelongsTo
    {
        return $this->belongsTo(RedemptionRequest::class, 'redemption_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
