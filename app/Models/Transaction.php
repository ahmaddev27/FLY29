<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'agent_id',
        'reference_id',
        'transaction_type',
        'amount_usd',
        'destination',
        'points_awarded',
        'config_snapshot',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd'       => 'decimal:2',
            'points_awarded'   => 'integer',
            'config_snapshot'  => 'array',
            'transaction_date' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function pointsHistory(): HasMany
    {
        return $this->hasMany(PointsHistory::class);
    }
}
