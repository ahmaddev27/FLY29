<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'key',
        'value',
        'value_type',
        'category',
        'description',
        'is_public',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Return value cast to its proper PHP type.
     */
    public function typedValue(): mixed
    {
        return match ($this->value_type) {
            'int'   => (int) $this->value,
            'float' => (float) $this->value,
            'bool'  => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json'  => json_decode($this->value, true),
            default => (string) $this->value,
        };
    }
}
