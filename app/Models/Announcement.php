<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'variant',
        'tier_filter',
        'country_filter',
        'send_email',
        'is_active',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tier_filter'    => 'array',
            'country_filter' => 'array',
            'send_email'     => 'boolean',
            'is_active'      => 'boolean',
            'expires_at'     => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    /**
     * Active = is_active true AND not yet expired.
     */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->where(function ($qb) {
                $qb->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Filter to announcements that target this agent (by tier+country).
     */
    public function scopeForAgent(Builder $q, Agent $agent): Builder
    {
        return $q->where(function ($qb) use ($agent) {
            $qb->whereNull('tier_filter')
               ->orWhereJsonContains('tier_filter', $agent->current_tier);
        })->where(function ($qb) use ($agent) {
            $qb->whereNull('country_filter')
               ->orWhereJsonContains('country_filter', $agent->country);
        });
    }

    /**
     * Returns the agents this announcement should be delivered to.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Agent>
     */
    public function recipients()
    {
        $query = Agent::query()
            ->with('user')
            ->whereHas('user', fn ($u) => $u->where('status', 'active'));

        if (! empty($this->tier_filter)) {
            $query->whereIn('current_tier', $this->tier_filter);
        }
        if (! empty($this->country_filter)) {
            $query->whereIn('country', $this->country_filter);
        }

        return $query->get();
    }
}
