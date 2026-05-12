<?php

use App\Http\Controllers\Manager\AdjustmentController;
use App\Http\Controllers\Manager\AgentController;
use App\Http\Controllers\Manager\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Account Manager Routes
|--------------------------------------------------------------------------
| Routes accessible only to authenticated users with role = account_manager.
| All routes share the prefix /manager and the name prefix "manager.".
| Row-level security: managers only see/act on their assigned agents.
*/

Route::middleware(['auth', 'manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // My assigned agents
        Route::controller(AgentController::class)
            ->prefix('agents')
            ->name('agents')
            ->group(function () {
                Route::get('/',          'index');
                Route::get('/{agent}',   'show')->name('.show');
            });

        // Suggest adjustments (always queued — admin approval required)
        Route::controller(AdjustmentController::class)
            ->prefix('adjustments')
            ->name('adjustments')
            ->group(function () {
                Route::get('/',                       'index');
                Route::post('/agent/{agent}',         'store')->name('.store');
                Route::post('/{adjustment}/cancel',   'cancel')->name('.cancel');
            });
    });
