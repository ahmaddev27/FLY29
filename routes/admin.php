<?php

use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\RedemptionController;
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

        // Dashboard (real one to come)
        Route::view('/dashboard', 'placeholders.admin')->name('dashboard');

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

        // Placeholders (real pages to come)
        Route::view('/agents',   'placeholders.admin')->name('agents');
        Route::view('/reports',  'placeholders.admin')->name('reports');
        Route::view('/settings', 'placeholders.admin')->name('settings');
        Route::view('/audit',    'placeholders.admin')->name('audit');
    });
