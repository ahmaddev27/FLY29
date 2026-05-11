<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierHistory extends Model
{
    protected $table = 'tier_history';

    public $timestamps = false;

    protected $fillable = [
        'agent_id',
        'from_tier',
        'to_tier',
        'action',
        'packages_at_time',
        'valid_until',
        'triggered_by',
        'admin_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'packages_at_time' => 'integer',
            'valid_until'      => 'datetime',
            'created_at'       => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
