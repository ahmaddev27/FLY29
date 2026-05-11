<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentLevel extends Model
{
    protected $fillable = [
        'tier_name',
        'min_packages_monthly',
        'points_per_package',
        'amount_per_point',
        'benefits',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'min_packages_monthly' => 'integer',
            'points_per_package'   => 'integer',
            'amount_per_point'     => 'decimal:2',
            'benefits'             => 'array',
            'display_order'        => 'integer',
        ];
    }

    public static function forTier(string $tier): ?self
    {
        return static::where('tier_name', $tier)->first();
    }
}
