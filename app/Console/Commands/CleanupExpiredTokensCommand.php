<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Daily cleanup of stale auth-related rows.
 */
class CleanupExpiredTokensCommand extends Command
{
    protected $signature   = 'tokens:cleanup';
    protected $description = 'Remove expired password-reset tokens and old session rows.';

    public function handle(): int
    {
        // Laravel's password_reset_tokens are valid for 60 min by default.
        $cutoff = now()->subHours(2);

        $resetDeleted = DB::table('password_reset_tokens')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Removed {$resetDeleted} expired password-reset token(s).");

        // If sessions are stored in DB, prune ones idle for >24h.
        $sessionLifetime = (int) config('session.lifetime', 120);
        $sessionDeleted  = 0;
        if (config('session.driver') === 'database') {
            $sessionDeleted = DB::table(config('session.table', 'sessions'))
                ->where('last_activity', '<', now()->subMinutes($sessionLifetime)->getTimestamp())
                ->delete();
            $this->info("Removed {$sessionDeleted} stale DB session row(s).");
        }

        return self::SUCCESS;
    }
}
