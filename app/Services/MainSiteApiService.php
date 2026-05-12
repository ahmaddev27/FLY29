<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Main Site (fly29.net) loyalty API.
 *
 * Endpoints expected on the Main Site (per docs/09_MAIN_SITE_API_SPEC):
 *   GET /api/v1/loyalty/transactions/summary?date=YYYY-MM-DD
 *   GET /api/v1/loyalty/transactions/list?date=YYYY-MM-DD&page=N
 *   GET /api/v1/loyalty/agents/{agent_id}
 *
 * The base URL + auth token are pulled from config so they can be
 * overridden in .env (MAIN_SITE_URL, MAIN_SITE_API_TOKEN) without
 * touching code. When the token is missing we short-circuit and return
 * empty/error results — useful for local dev where the Main Site isn't
 * reachable.
 */
class MainSiteApiService
{
    private string $baseUrl;
    private ?string $token;
    private int $timeoutSeconds;

    public function __construct()
    {
        $this->baseUrl        = rtrim((string) config('services.main_site.url', env('MAIN_SITE_URL', '')), '/');
        $this->token          = config('services.main_site.token', env('MAIN_SITE_API_TOKEN'));
        $this->timeoutSeconds = (int) config('services.main_site.timeout', 10);
    }

    /**
     * Daily transaction totals for reconciliation.
     *
     * @return array{date:string, count:int, total_amount_usd:float}
     */
    public function dailySummary(string $date): array
    {
        $response = $this->get('/api/v1/loyalty/transactions/summary', ['date' => $date]);

        return [
            'date'             => $response['date'] ?? $date,
            'count'            => (int) ($response['count'] ?? 0),
            'total_amount_usd' => (float) ($response['total_amount_usd'] ?? 0),
        ];
    }

    /**
     * Paginated transaction list — used when summary numbers mismatch and
     * we want to identify the actually-missing reference_ids.
     *
     * @return array{data: array<int, array<string, mixed>>, pagination: array}
     */
    public function dailyList(string $date, int $page = 1): array
    {
        $response = $this->get('/api/v1/loyalty/transactions/list', [
            'date' => $date,
            'page' => $page,
        ]);

        return [
            'data'       => $response['data']       ?? [],
            'pagination' => $response['pagination'] ?? [],
        ];
    }

    /**
     * Verify an agent exists on the Main Site by external id.
     */
    public function verifyAgent(string $externalAgentId): ?array
    {
        try {
            return $this->get("/api/v1/loyalty/agents/{$externalAgentId}");
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Internal GET — throws on failure so callers can decide how to handle.
     */
    private function get(string $path, array $query = []): array
    {
        if (! $this->baseUrl || ! $this->token) {
            throw new \RuntimeException('Main Site API not configured (MAIN_SITE_URL / MAIN_SITE_API_TOKEN missing).');
        }

        $response = Http::timeout($this->timeoutSeconds)
            ->withToken($this->token)
            ->acceptJson()
            ->get($this->baseUrl . $path, $query);

        if ($response->failed()) {
            Log::warning('Main Site API error', [
                'path'   => $path,
                'query'  => $query,
                'status' => $response->status(),
                'body'   => mb_substr($response->body(), 0, 500),
            ]);
            throw new \RuntimeException("Main Site API returned HTTP {$response->status()} for {$path}");
        }

        return $response->json() ?? [];
    }
}
