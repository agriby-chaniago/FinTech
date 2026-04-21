<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\Auth\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.attempt');
});

Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/', function (): \Illuminate\View\View {
        return view('welcome');
    })->name('home');

    Route::prefix('dashboard')->group(function (): void {
        Route::post('/analyze/auto/run', [AnalysisController::class, 'analyzeAutoRun'])
            ->name('dashboard.analyze.auto.run');

        Route::post('/analyze/send-service-c', [AnalysisController::class, 'sendToServiceC'])
            ->name('dashboard.analyze.send-service-c');
    });
});
