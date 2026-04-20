<?php

use App\Http\Controllers\AnalysisController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->group(function (): void {
    Route::post('/analyze/auto/run', [AnalysisController::class, 'analyzeAutoRun'])
        ->name('dashboard.analyze.auto.run');

    Route::post('/analyze/send-service-c', [AnalysisController::class, 'sendToServiceC'])
        ->name('dashboard.analyze.send-service-c');
});

Route::get('/', function (): \Illuminate\View\View {
    return view('welcome');
});
