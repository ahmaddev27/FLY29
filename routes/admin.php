<?php

use App\Http\Controllers\Admin\AdjustmentController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\RedemptionController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Routes accessible only to authenticated users with role = admin
| or super_admin. All routes share the prefix /admin and the name
| prefix "admin.".
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Agents management
        Route::controller(AgentController::class)
            ->prefix('agents')
            ->name('agents')
            ->group(function () {
                Route::get('/',                       'index');
                Route::get('/create',                 'create')->name('.create');
                Route::post('/',                      'store')->name('.store');
                Route::get('/import',                 'importForm')->name('.import');
                Route::post('/import',                'import')->name('.import.store');
                Route::get('/import/template',        'importTemplate')->name('.import.template');
                Route::get('/{agent}',                'show')->name('.show');
                Route::patch('/{agent}/suspend',      'suspend')->name('.suspend');
                Route::patch('/{agent}/unsuspend',    'unsuspend')->name('.unsuspend');
                Route::patch('/{agent}/notes',        'updateNotes')->name('.notes');
                Route::delete('/{agent}',             'destroy')->name('.destroy');
            });

        // Redemption requests review + approve/reject
        Route::controller(RedemptionController::class)
            ->prefix('redemptions')
            ->name('redemptions')
            ->group(function () {
                Route::get('/',                          'index');
                Route::post('/{redemption}/approve',     'approve')->name('.approve');
                Route::post('/{redemption}/reject',      'reject')->name('.reject');
            });

        // Free packages CRUD
        Route::controller(PackageController::class)
            ->prefix('packages')
            ->name('packages')
            ->group(function () {
                Route::get('/',                       'index');
                Route::get('/create',                 'create')->name('.create');
                Route::post('/',                      'store')->name('.store');
                Route::get('/{package}/edit',         'edit')->name('.edit');
                Route::put('/{package}',              'update')->name('.update');
                Route::patch('/{package}/toggle',     'toggle')->name('.toggle');
                Route::delete('/{package}',           'destroy')->name('.destroy');
            });

        // Settings
        Route::get('/settings',    [SettingsController::class, 'index'])->name('settings');
        Route::patch('/settings',  [SettingsController::class, 'update'])->name('settings.update');

        // Manual adjustments (with dual approval over threshold)
        Route::controller(AdjustmentController::class)
            ->prefix('adjustments')
            ->name('adjustments')
            ->group(function () {
                Route::get('/',                       'index');
                Route::post('/agent/{agent}',         'store')->name('.store');
                Route::post('/{adjustment}/approve',  'approve')->name('.approve');
                Route::post('/{adjustment}/reject',   'reject')->name('.reject');
                Route::post('/{adjustment}/cancel',   'cancel')->name('.cancel');
            });

        // Placeholders (real pages to come)
        Route::view('/reports',  'placeholders.admin')->name('reports');
        Route::view('/audit',    'placeholders.admin')->name('audit');
    });
