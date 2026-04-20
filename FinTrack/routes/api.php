<?php

use App\Http\Controllers\Api\FinanceSummaryController;
use App\Http\Controllers\Api\Service2PullController;
use App\Http\Controllers\Api\Service3PlanResultController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['hybrid.api_auth', 'auth.principal'])->group(function (): void {
    Route::apiResource('transactions', TransactionController::class)
        ->names('api.transactions');

    Route::get('/users/{user}/service3/plans', [Service3PlanResultController::class, 'index'])
        ->whereNumber('user');
    Route::get('/users/{user}/service3/plans/latest', [Service3PlanResultController::class, 'latest'])
        ->whereNumber('user');

    Route::middleware('service.api_key')
        ->get('/finance/summary', [FinanceSummaryController::class, 'summary']);
});

Route::middleware('service.api_key:service2_pull')
    ->get('/service2/users/{user}/transactions-feed', [Service2PullController::class, 'transactionsFeed'])
    ->whereNumber('user');

Route::middleware('service.api_key:service3_callback')
    ->post('/service3/plans/callback', [Service3PlanResultController::class, 'callback']);
