<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'sms_enabled',
        'in_app_enabled',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled'  => 'boolean',
            'sms_enabled'    => 'boolean',
            'in_app_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
