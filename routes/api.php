<?php

use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
| All routes here are stateless (no session) and prefixed with /api.
| Webhooks use API Key + HMAC auth via dedicated middlewares.
*/

Route::prefix('v1')->group(function () {

    // Public health check (no auth)
    Route::get('/health', [WebhookController::class, 'health'])->name('api.health');

    // Webhook ingestion (auth + signature + rate limit)
    Route::post('/transactions/ingest', [WebhookController::class, 'ingest'])
        ->middleware([
            'webhook.auth',
            'webhook.signature',
            'webhook.log',
            'throttle:webhook',
        ])
        ->name('api.transactions.ingest');
});
