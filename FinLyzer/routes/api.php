<?php

use App\Http\Controllers\AnalysisController;
use Illuminate\Support\Facades\Route;

Route::middleware('hybrid.api_auth')->group(function (): void {
	Route::post('/analyze', [AnalysisController::class, 'analyze']);
	Route::post('/analyze/auto', [AnalysisController::class, 'analyzeAuto']);
	Route::post('/analyze/auto/run', [AnalysisController::class, 'analyzeAutoRun']);
	Route::get('/analyze/auto/latest', [AnalysisController::class, 'latestForServiceC']);
});
