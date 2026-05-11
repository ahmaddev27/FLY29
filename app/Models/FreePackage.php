<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreePackage extends Model
{
    protected $fillable = [
        'name',
        'destination',
        'points_required',
        'duration_days',
        'description',
        'image_url',
        'valid_until',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'points_required' => 'integer',
            'duration_days'   => 'integer',
            'is_active'       => 'boolean',
            'valid_until'     => 'datetime',
            'display_order'   => 'integer',
        ];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(RedemptionRequest::class, 'package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>', now());
            });
    }
}
