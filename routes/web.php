<?php

use App\Http\Controllers\Admin\RedemptionController as AdminRedemption;
use App\Http\Controllers\Agent\DashboardController as AgentDashboard;
use App\Http\Controllers\Agent\NotificationController as AgentNotifications;
use App\Http\Controllers\Agent\NotificationPreferencesController as AgentNotificationPrefs;
use App\Http\Controllers\Agent\ProfileController as AgentProfile;
use App\Http\Controllers\Agent\RedemptionController as AgentRedemption;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')->name('login.store');

    Route::get('/forgot-password',  [PasswordResetController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->middleware('throttle:password-reset')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password',          [PasswordResetController::class, 'update'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated common
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Agent routes (role=agent only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'agent'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/dashboard', [AgentDashboard::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile',           [AgentProfile::class, 'show'])->name('profile');
    Route::put('/profile',           [AgentProfile::class, 'update'])->name('profile.update');
    Route::put('/profile/password',  [AgentProfile::class, 'password'])->name('profile.password');

    // Notification preferences
    Route::get('/notification-preferences', [AgentNotificationPrefs::class, 'show'])->name('notification-preferences');
    Route::put('/notification-preferences', [AgentNotificationPrefs::class, 'update'])->name('notification-preferences.update');

    // Notifications (AJAX)
    Route::get('/notifications',                       [AgentNotifications::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [AgentNotifications::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/notifications/read-all',            [AgentNotifications::class, 'markAllAsRead'])->name('notifications.read-all');

    // Redemptions (cash + my requests)
    Route::get('/redemptions',                          [AgentRedemption::class, 'index'])->name('redemptions.index');
    Route::get('/redemptions/cash',                     [AgentRedemption::class, 'cashForm'])->name('redemptions.cash');
    Route::post('/redemptions/cash',                    [AgentRedemption::class, 'storeCash'])->name('redemptions.cash.store');
    Route::delete('/redemptions/{redemption}',          [AgentRedemption::class, 'destroy'])->name('redemptions.cancel');

    // Legacy alias for sidebar
    Route::get('/redemptions-list', fn() => redirect()->route('agent.redemptions.index'))->name('redemptions');

    // Placeholders (Sprint 2.2+)
    Route::view('/wallets',                'placeholders.agent')->name('wallets');
    Route::view('/redemptions/packages',   'placeholders.agent')->name('redemptions.packages');
    Route::view('/transactions',           'placeholders.agent')->name('transactions');
    Route::view('/messages',               'placeholders.agent')->name('messages');
    Route::view('/tiers',                  'placeholders.agent')->name('tiers');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'placeholders.admin')->name('dashboard');

    // Redemptions
    Route::get('/redemptions',                          [AdminRedemption::class, 'index'])->name('redemptions');
    Route::post('/redemptions/{redemption}/approve',    [AdminRedemption::class, 'approve'])->name('redemptions.approve');
    Route::post('/redemptions/{redemption}/reject',     [AdminRedemption::class, 'reject'])->name('redemptions.reject');

    // Placeholders (Sprint 3.x)
    Route::view('/agents',   'placeholders.admin')->name('agents');
    Route::view('/packages', 'placeholders.admin')->name('packages');
    Route::view('/reports',  'placeholders.admin')->name('reports');
    Route::view('/settings', 'placeholders.admin')->name('settings');
    Route::view('/audit',    'placeholders.admin')->name('audit');
});

Route::middleware('auth')->group(function () {
    Route::view('/manager/dashboard', 'placeholders.manager')->name('manager.dashboard');
});

/*
|--------------------------------------------------------------------------
| Design System showcase (dev only)
|--------------------------------------------------------------------------
*/
Route::view('/design-system', 'design-system')->name('design-system');
