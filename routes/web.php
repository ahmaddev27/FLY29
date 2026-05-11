<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public + global
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

// Design system showcase (dev / internal)
Route::view('/design-system', 'design-system')->name('design-system');

/*
|--------------------------------------------------------------------------
| Guest auth (login, forgot/reset password)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::controller(LoginController::class)->group(function () {
        Route::get('/login',  'show')->name('login');
        Route::post('/login', 'store')
            ->middleware('throttle:login')
            ->name('login.store');
    });

    Route::controller(PasswordResetController::class)->group(function () {
        Route::get('/forgot-password',          'showLinkRequest')->name('password.request');
        Route::post('/forgot-password',         'sendLink')
            ->middleware('throttle:password-reset')
            ->name('password.email');
        Route::get('/reset-password/{token}',   'showReset')->name('password.reset');
        Route::post('/reset-password',          'update')->name('password.update');
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated — shared (logout)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Role-specific route files
|--------------------------------------------------------------------------
| Loaded automatically by bootstrap/app.php (see the `then:` callback):
|   - routes/agent.php   → /agent/*
|   - routes/admin.php   → /admin/*
|   - routes/manager.php → /manager/*
*/
