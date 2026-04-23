<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\Auth\OidcController;
use App\Http\Controllers\Auth\SessionController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): \Illuminate\View\View|RedirectResponse {
    if ((bool) config('keycloak.enabled', false) && ! Auth::check()) {
        return redirect()->route('oidc.redirect');
    }

    if (Auth::check()) {
        return view('welcome');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::get('/register', [OidcController::class, 'register'])->name('register');
    Route::get('/auth/oidc/redirect', [OidcController::class, 'redirect'])->name('oidc.redirect');
    Route::get('/auth/oidc/login', [OidcController::class, 'redirect'])->name('oidc.login');
    Route::get('/auth/oidc/register', [OidcController::class, 'register'])->name('oidc.register');
    Route::get('/auth/oidc/callback', [OidcController::class, 'callback'])->name('oidc.callback');
});

Route::post('/logout', [OidcController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::post('/auth/oidc/logout', [OidcController::class, 'logout'])
    ->middleware('auth')
    ->name('oidc.logout');

Route::middleware('auth')->group(function (): void {
    Route::prefix('dashboard')->group(function (): void {
        Route::post('/analyze/auto/run', [AnalysisController::class, 'analyzeAutoRun'])
            ->name('dashboard.analyze.auto.run');

        Route::post('/analyze/send-service-c', [AnalysisController::class, 'sendToServiceC'])
            ->name('dashboard.analyze.send-service-c');
    });
});
