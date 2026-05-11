<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Guest routes (auth)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Login (throttle: 20/min/IP + 10/min/email — defined in AppServiceProvider)
    Route::get('/login',  [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    // Forgot / Reset password (throttle: 5/min/IP)
    Route::get('/forgot-password',  [PasswordResetController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->middleware('throttle:password-reset')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password',          [PasswordResetController::class, 'update'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Placeholder dashboards (real ones in Sprint 1.3 / 3.1)
    Route::view('/agent/dashboard',   'placeholders.agent')->name('agent.dashboard');
    Route::view('/admin/dashboard',   'placeholders.admin')->name('admin.dashboard');
    Route::view('/manager/dashboard', 'placeholders.manager')->name('manager.dashboard');
});

// Design System showcase (will be removed in production)
Route::view('/design-system', 'design-system')->name('design-system');
