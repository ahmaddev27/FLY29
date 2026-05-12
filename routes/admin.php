<?php

use App\Http\Controllers\Admin\AccountManagerController;
use App\Http\Controllers\Admin\AdjustmentController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\ApiLogController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\RedemptionController;
use App\Http\Controllers\Admin\ReportController;
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
                Route::post('/bulk-approve',             'bulkApprove')->name('.bulk-approve');
                Route::post('/bulk-reject',              'bulkReject')->name('.bulk-reject');
                Route::post('/{redemption}/approve',     'approve')->name('.approve');
                Route::post('/{redemption}/reject',      'reject')->name('.reject');
                Route::post('/{redemption}/fulfill',     'fulfill')->name('.fulfill');
                Route::post('/{redemption}/reverse-fulfillment', 'reverseFulfillment')->name('.reverse-fulfillment');
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
        Route::get('/settings',                [SettingsController::class, 'index'])->name('settings');
        Route::patch('/settings',              [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-email',    [SettingsController::class, 'sendTestEmail'])->name('settings.test-email');

        // Account Managers
        Route::controller(AccountManagerController::class)
            ->prefix('account-managers')
            ->name('account-managers')
            ->group(function () {
                Route::get('/',                              'index');
                Route::get('/create',                        'create')->name('.create');
                Route::post('/',                             'store')->name('.store');
                Route::get('/{manager}',                     'show')->name('.show');
                Route::post('/{manager}/assign',             'assign')->name('.assign');
                Route::delete('/{manager}/agents/{agent}',   'unassign')->name('.unassign');
                Route::patch('/{manager}/suspend',           'suspend')->name('.suspend');
                Route::patch('/{manager}/unsuspend',         'unsuspend')->name('.unsuspend');
                Route::delete('/{manager}',                  'destroy')->name('.destroy');
            });

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

        // Announcements (broadcast to agents)
        Route::controller(AnnouncementController::class)
            ->prefix('announcements')
            ->name('announcements')
            ->group(function () {
                Route::get('/',                        'index');
                Route::get('/create',                  'create')->name('.create');
                Route::post('/',                       'store')->name('.store');
                Route::patch('/{announcement}/toggle', 'toggle')->name('.toggle');
                Route::delete('/{announcement}',       'destroy')->name('.destroy');
            });

        // Audit log (super_admin only — enforced inside controller)
        Route::get('/audit',     [AuditLogController::class, 'index'])->name('audit');

        // Webhook / API request logs
        Route::get('/api-logs',           [ApiLogController::class, 'index'])->name('api-logs');
        Route::get('/api-logs/{log}',     [ApiLogController::class, 'show'])->name('api-logs.show');

        // Reports
        Route::controller(ReportController::class)
            ->prefix('reports')
            ->name('reports')
            ->group(function () {
                Route::get('/',                'index');
                Route::get('/points',          'points')->name('.points');
                Route::get('/points/pdf',      'pointsPdf')->name('.points.pdf');
                Route::get('/sales',           'sales')->name('.sales');
                Route::get('/sales/pdf',       'salesPdf')->name('.sales.pdf');
                Route::get('/tiers',           'tiers')->name('.tiers');
                Route::get('/redemptions',     'redemptions')->name('.redemptions');
                Route::get('/top-agents',      'topAgents')->name('.top-agents');
                Route::get('/top-agents/xlsx', 'topAgentsExcel')->name('.top-agents.xlsx');
            });
    });
