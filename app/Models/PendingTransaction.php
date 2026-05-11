<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingTransaction extends Model
{
    protected $fillable = [
        'external_agent_id',
        'reference_id',
        'payload',
        'reason',
        'processed',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'      => 'array',
            'processed'    => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }
}
