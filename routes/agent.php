<?php

use App\Http\Controllers\Agent\DashboardController;
use App\Http\Controllers\Agent\NotificationPreferencesController;
use App\Http\Controllers\Agent\PackageController;
use App\Http\Controllers\Agent\ProfileController;
use App\Http\Controllers\Agent\RedemptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Agent Routes
|--------------------------------------------------------------------------
| Routes accessible only to authenticated users with role = agent.
| All routes share the prefix /agent and the name prefix "agent.".
*/

Route::middleware(['auth', 'agent'])
    ->prefix('agent')
    ->name('agent.')
    ->group(function () {

        // Dashboard
        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
        });

        // Profile + password
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile',          'show')->name('profile');
            Route::put('/profile',          'update')->name('profile.update');
            Route::put('/profile/password', 'password')->name('profile.password');
        });

        // Notification preferences
        Route::controller(NotificationPreferencesController::class)->group(function () {
            Route::get('/notification-preferences', 'show')->name('notification-preferences');
            Route::put('/notification-preferences', 'update')->name('notification-preferences.update');
        });

        // Cash redemption + my requests
        Route::controller(RedemptionController::class)
            ->prefix('redemptions')
            ->name('redemptions.')
            ->group(function () {
                Route::get('/',              'index')->name('index');
                Route::get('/cash',          'cashForm')->name('cash');
                Route::post('/cash',         'storeCash')->name('cash.store');
                Route::delete('/{redemption}', 'destroy')->name('cancel');
            });

        // Free packages (listing + instant redeem)
        Route::controller(PackageController::class)->group(function () {
            Route::get('/redemptions/packages',                       'index')->name('redemptions.packages');
            Route::post('/redemptions/packages/{package}/redeem',     'redeem')->name('packages.redeem');
        });

        // Convenience alias used by the sidebar
        Route::redirect('/redemptions-list', '/agent/redemptions')->name('redemptions');

        // Placeholders (real pages to come)
        Route::view('/wallets',      'placeholders.agent')->name('wallets');
        Route::view('/transactions', 'placeholders.agent')->name('transactions');
        Route::view('/messages',     'placeholders.agent')->name('messages');
        Route::view('/tiers',        'placeholders.agent')->name('tiers');
    });
