<?php

use App\Http\Controllers\Auth\OidcController;
use App\Http\Controllers\AnalysisController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/auth/oidc/redirect', [OidcController::class, 'redirect'])
        ->name('oidc.redirect');

    Route::get('/auth/oidc/callback', [OidcController::class, 'callback'])
        ->name('oidc.callback');
});

Route::post('/auth/oidc/logout', [OidcController::class, 'logout'])
    ->name('oidc.logout');

Route::get('/', function (): \Illuminate\Http\RedirectResponse|\Illuminate\View\View {
    if (! (bool) config('keycloak.enabled', false)) {
        return view('welcome');
    }

    if (Auth::check()) {
        return view('welcome');
    }

    return redirect()->route('oidc.redirect');
});

Route::middleware('hybrid.web_auth')->prefix('dashboard')->group(function (): void {
    Route::post('/analyze/auto/run', [AnalysisController::class, 'analyzeAutoRun'])
        ->name('dashboard.analyze.auto.run');

    Route::post('/analyze/send-service-c', [AnalysisController::class, 'sendToServiceC'])
        ->name('dashboard.analyze.send-service-c');
});
