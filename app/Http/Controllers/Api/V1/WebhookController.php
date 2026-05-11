<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\IngestTransactionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IngestTransactionRequest;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    public function __construct(private IngestTransactionAction $ingest) {}

    /**
     * POST /api/v1/transactions/ingest
     *
     * Headers (validated by middleware):
     *   - X-API-Key
     *   - X-Signature: sha256=<hmac>
     *
     * Returns JSON shape per `09_MAIN_SITE_API_SPEC.md`:
     *   200 accepted | duplicate_ignored | agent_suspended
     *   404 agent_not_found
     *   422 validation_failed (via FormRequest)
     */
    public function ingest(IngestTransactionRequest $request): JsonResponse
    {
        $result = $this->ingest->execute($request->validated());

        return match ($result['status']) {
            'accepted' => response()->json([
                'status'         => 'accepted',
                'transaction_id' => $result['transaction']->id,
                'reference_id'   => $result['transaction']->reference_id,
                'points_awarded' => $result['points_awarded'],
                'new_balance'    => $result['new_balance'],
                'upgraded_to'    => $result['upgraded_to'],
            ], 200),

            'duplicate_ignored' => response()->json([
                'status'       => 'duplicate_ignored',
                'reference_id' => $result['reference_id'],
                'message'      => 'Transaction already processed.',
            ], 200),

            'agent_not_found' => response()->json([
                'status'   => 'agent_not_found',
                'agent_id' => $request->input('agent_id'),
            ], 404),

            'agent_suspended' => response()->json([
                'status'            => 'agent_suspended',
                'transaction_held'  => true,
                'message'           => 'Transaction saved for later processing when agent is reactivated.',
            ], 422),

            default => response()->json([
                'status'  => 'server_error',
                'message' => 'Unexpected ingestion state.',
            ], 500),
        };
    }

    /**
     * GET /api/v1/health
     * Public, no auth — for Main Site monitoring.
     */
    public function health(): JsonResponse
    {
        $checks = [
            'database' => 'ok',
        ];
        $overall = 'ok';

        try {
            \DB::connection()->getPdo();
        } catch (\Throwable) {
            $checks['database'] = 'fail';
            $overall = 'degraded';
        }

        return response()->json([
            'status'    => $overall,
            'version'   => '1.0.0',
            'timestamp' => now()->toIso8601String(),
            'checks'    => $checks,
        ], $overall === 'ok' ? 200 : 503);
    }
}
