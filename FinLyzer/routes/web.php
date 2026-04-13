<?php

use App\Http\Controllers\Auth\OidcController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/auth/oidc/redirect', [OidcController::class, 'redirect'])
        ->name('oidc.redirect');

    Route::get('/auth/oidc/callback', [OidcController::class, 'callback'])
        ->name('oidc.callback');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/auth/oidc/logout', [OidcController::class, 'logout'])
        ->name('oidc.logout');
});
