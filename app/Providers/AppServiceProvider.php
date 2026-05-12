<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiters();
        $this->applyDbMailConfig();
    }

    /**
     * Override Laravel's SMTP config from system_settings if the admin has
     * enabled the mail toggle. Wrapped in a try/catch so it doesn't break
     * early-boot scenarios (e.g. fresh install before migrations).
     */
    private function applyDbMailConfig(): void
    {
        try {
            $this->app->make(\App\Services\MailConfigService::class)->applyFromSettings();
        } catch (\Throwable) {
            // Settings table not ready yet, or any other boot-time issue.
        }
    }

    /**
     * Application-wide named rate limiters.
     *
     * Login: 20 POST/min/IP + 10 POST/min/(email+IP) — generous enough that
     * legitimate users won't trip it, but blocks brute-force attempts.
     * The real lockout (5 failed attempts in 15 min → DB-level lock) lives
     * in AuthService.
     */
    private function configureRateLimiters(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(20)->by('login-ip:' . $request->ip()),
                Limit::perMinute(10)->by('login-email:' . $email . '|' . $request->ip()),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return [
                Limit::perMinute(5)->by('pwd-reset:' . $request->ip()),
            ];
        });

        // Webhook ingestion: per-API-key (configurable via system_settings later).
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(100)->by('webhook:' . $request->header('X-API-Key', 'anonymous'));
        });
    }
}
