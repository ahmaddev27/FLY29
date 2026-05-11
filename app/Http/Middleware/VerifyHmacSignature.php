<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies X-Signature header (sha256=<hex>) against HMAC-SHA256 of raw body.
 * Skips verification if `webhook_signature_verification` system setting is false
 * (useful only for early dev — never disable in production).
 */
class VerifyHmacSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $verifyEnabled = app(\App\Services\SettingsService::class)
            ->get('webhook_signature_verification', true);

        if (! $verifyEnabled) {
            return $next($request);
        }

        $providedSignature = (string) $request->header('X-Signature', '');
        $secret            = (string) config('services.main_site.webhook_secret');

        if ($secret === '' || $providedSignature === '') {
            return response()->json([
                'status' => 'unauthorized',
                'error'  => 'missing_signature',
            ], 401);
        }

        $rawBody  = $request->getContent();
        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);

        if (! hash_equals($expected, $providedSignature)) {
            return response()->json([
                'status' => 'unauthorized',
                'error'  => 'invalid_signature',
            ], 401);
        }

        return $next($request);
    }
}
