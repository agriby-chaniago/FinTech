<?php

use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\PlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('hybrid.api_auth')->group(function (): void {
    Route::post('/plan', [PlanController::class, 'store']);
    Route::apiResource('goals', GoalController::class);
});
