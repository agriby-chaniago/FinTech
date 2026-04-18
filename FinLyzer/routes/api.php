<?php

use App\Http\Controllers\AnalysisController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->prefix('internal')->group(function (): void {
	Route::post('/analyze', [AnalysisController::class, 'analyze']);
	Route::post('/analyze/auto', [AnalysisController::class, 'analyzeAuto']);
	Route::post('/analyze/auto/run', [AnalysisController::class, 'analyzeAutoRun']);
	Route::post('/analyze/send-service-c', [AnalysisController::class, 'sendToServiceC']);
	Route::get('/analyze/auto/latest', [AnalysisController::class, 'latestForServiceC']);
});

Route::middleware(['hybrid.api_auth', 'auth.principal'])->prefix('user')->group(function (): void {
	Route::get('/analyze/auto/latest', [AnalysisController::class, 'latestForServiceC']);
});

Route::middleware('api.key')->group(function (): void {
	Route::post('/analyze', [AnalysisController::class, 'analyze']);
	Route::post('/analyze/auto', [AnalysisController::class, 'analyzeAuto']);
	Route::post('/analyze/auto/run', [AnalysisController::class, 'analyzeAutoRun']);
	Route::post('/analyze/send-service-c', [AnalysisController::class, 'sendToServiceC']);
	Route::get('/analyze/auto/latest', [AnalysisController::class, 'latestForServiceC']);
});
