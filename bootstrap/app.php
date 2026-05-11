<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
        then: function () {
            // Role-specific route files; each runs under the default
            // web middleware group (session, csrf, etc.).
            Route::middleware('web')->group(base_path('routes/agent.php'));
            Route::middleware('web')->group(base_path('routes/admin.php'));
            Route::middleware('web')->group(base_path('routes/manager.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'webhook.auth'      => \App\Http\Middleware\WebhookAuth::class,
            'webhook.signature' => \App\Http\Middleware\VerifyHmacSignature::class,
            'webhook.log'       => \App\Http\Middleware\ApiLog::class,
            'agent'             => \App\Http\Middleware\EnsureAgent::class,
            'admin'             => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
