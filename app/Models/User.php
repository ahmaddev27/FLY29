<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'role',
        'email',
        'password',
        'full_name',
        'phone',
        'status',
        'two_factor_secret',
        'two_factor_enabled',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'last_login_ip',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'two_factor_enabled'   => 'boolean',
            'failed_login_attempts' => 'integer',
            'locked_until'         => 'datetime',
            'last_login_at'        => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class);
    }

    public function managedAgents(): HasMany
    {
        return $this->hasMany(Agent::class, 'account_manager_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isAccountManager(): bool
    {
        return $this->role === 'account_manager';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }
}
