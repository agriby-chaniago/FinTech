<?php

use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\PlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function (): void {
    Route::post('/plan', [PlanController::class, 'store']);
});

Route::middleware('api.key')->prefix('internal')->group(function (): void {
    Route::post('/plan', [PlanController::class, 'store']);
});

Route::middleware('hybrid.api_auth')->group(function (): void {
    Route::apiResource('goals', GoalController::class);
});
