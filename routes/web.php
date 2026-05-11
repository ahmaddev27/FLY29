<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Design System showcase (will be removed in production)
Route::view('/design-system', 'design-system')->name('design-system');
