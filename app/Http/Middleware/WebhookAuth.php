<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the X-API-Key header against the configured webhook key.
 * Uses constant-time comparison to prevent timing attacks.
 *
 * Stores the verified key (masked) on the request for later logging.
 */
class WebhookAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = (string) $request->header('X-API-Key', '');
        $expectedKey = (string) config('services.main_site.api_key');

        if ($expectedKey === '' || ! hash_equals($expectedKey, $providedKey)) {
            return response()->json([
                'status' => 'unauthorized',
                'error'  => 'invalid_api_key',
            ], 401);
        }

        // Attach a masked identifier for downstream logging.
        $request->attributes->set(
            'api_key_used',
            substr($providedKey, 0, 8) . '…' . substr($providedKey, -4)
        );

        return $next($request);
    }
}
