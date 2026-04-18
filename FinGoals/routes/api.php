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

Route::middleware(['hybrid.api_auth', 'auth.principal'])->prefix('user')->group(function (): void {
    Route::post('/plan', [PlanController::class, 'store'])->name('api.user.plan.store');
    Route::apiResource('goals', GoalController::class)->names('api.user.goals');
});

Route::middleware(['hybrid.api_auth', 'auth.principal'])->group(function (): void {
    Route::apiResource('goals', GoalController::class);
});
