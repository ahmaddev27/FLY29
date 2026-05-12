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
        $this->registerPdfArabicFont();
    }

    /**
     * Make the Cairo font (shipped in storage/fonts) available to dompdf so
     * Arabic glyphs render with proper shaping in PDF exports.
     */
    private function registerPdfArabicFont(): void
    {
        $regular = storage_path('fonts/Cairo-Regular.ttf');
        $bold    = storage_path('fonts/Cairo-Bold.ttf');

        if (! file_exists($regular)) {
            return;
        }

        // Register with the global Dompdf FontMetrics on first use.
        // We do this lazily because Dompdf may not be loaded on every request.
        $this->app->resolving(\Barryvdh\DomPDF\PDF::class, function ($pdf) use ($regular, $bold) {
            $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
            $fontMetrics->registerFont(
                ['family' => 'cairo', 'style' => 'normal', 'weight' => 'normal'],
                $regular,
            );
            if (file_exists($bold)) {
                $fontMetrics->registerFont(
                    ['family' => 'cairo', 'style' => 'normal', 'weight' => 'bold'],
                    $bold,
                );
            }
        });
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
