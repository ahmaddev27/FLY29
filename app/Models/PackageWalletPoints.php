<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageWalletPoints extends Model
{
    protected $table = 'package_wallet_points';

    protected $fillable = [
        'agent_id',
        'available_points',
        'locked_points',
        'lifetime_earned',
        'lifetime_redeemed',
    ];

    protected function casts(): array
    {
        return [
            'available_points'  => 'integer',
            'locked_points'     => 'integer',
            'lifetime_earned'   => 'integer',
            'lifetime_redeemed' => 'integer',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function totalPoints(): int
    {
        return $this->available_points + $this->locked_points;
    }
}
