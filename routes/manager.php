<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Account Manager Routes
|--------------------------------------------------------------------------
| Routes accessible only to authenticated users with role = account_manager.
| All routes share the prefix /manager and the name prefix "manager.".
|
| Full controllers will be added when the AM panel is implemented.
*/

Route::middleware('auth')
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {

        Route::view('/dashboard', 'placeholders.manager')->name('dashboard');
    });
