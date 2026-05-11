<?php

namespace App\Http\Middleware;

use App\Models\ApiLog as ApiLogModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs every API request/response into `api_logs` table.
 * Stores duration_ms, status (success/unauthorized/etc.), and reference_id if present.
 */
class ApiLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $durationMs = (int) ((microtime(true) - $startedAt) * 1000);

        try {
            ApiLogModel::create([
                'method'         => $request->method(),
                'endpoint'       => $request->path(),
                'request_headers' => $this->sanitizeHeaders($request->headers->all()),
                'request_body'   => $this->safeJsonBody($request),
                'response_code'  => $response->getStatusCode(),
                'response_body'  => $this->parseResponseBody($response),
                'api_key_used'   => $request->attributes->get('api_key_used'),
                'ip_address'     => $request->ip(),
                'duration_ms'    => $durationMs,
                'reference_id'   => $request->input('reference_id'),
                'status'         => $this->classify($response->getStatusCode(), $response),
            ]);
        } catch (\Throwable $e) {
            // Never let logging break the response.
            logger()->error('ApiLog middleware failed', ['error' => $e->getMessage()]);
        }

        return $response;
    }

    private function sanitizeHeaders(array $headers): array
    {
        // Mask sensitive headers
        $sensitive = ['x-api-key', 'authorization', 'cookie', 'x-signature'];
        foreach ($sensitive as $key) {
            if (isset($headers[$key])) {
                $headers[$key] = ['***masked***'];
            }
        }

        return $headers;
    }

    private function safeJsonBody(Request $request): ?array
    {
        try {
            return $request->json()->all() ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseResponseBody(Response $response): ?array
    {
        $content = $response->getContent();
        if (! $content) {
            return null;
        }
        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function classify(int $code, Response $response): string
    {
        if ($code === 401) {
            return 'unauthorized';
        }
        if ($code === 429) {
            return 'rate_limited';
        }

        $body = $this->parseResponseBody($response);
        if (isset($body['status']) && $body['status'] === 'duplicate_ignored') {
            return 'duplicate_ignored';
        }

        if ($code >= 200 && $code < 300) {
            return 'success';
        }

        return 'failed';
    }
}
