<?php

use App\Http\Controllers\Auth\OidcController;
use App\Http\Controllers\Web\GoalPageController;
use App\Http\Controllers\Web\PlannerPageController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/auth/oidc/redirect', [OidcController::class, 'redirect'])
        ->name('oidc.redirect');

    Route::get('/auth/oidc/callback', [OidcController::class, 'callback'])
        ->name('oidc.callback');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/auth/oidc/logout', [OidcController::class, 'logout'])
        ->name('oidc.logout');
});

Route::redirect('/', '/planner');

Route::name('web.')->group(function (): void {
    Route::get('/planner', [PlannerPageController::class, 'index'])->name('planner.index');
    Route::post('/planner', [PlannerPageController::class, 'store'])->name('planner.store');

    Route::prefix('goals')->name('goals.')->group(function (): void {
        Route::get('/', [GoalPageController::class, 'index'])->name('index');
        Route::post('/', [GoalPageController::class, 'store'])->name('store');
        Route::get('/{goalId}/edit', [GoalPageController::class, 'edit'])->name('edit');
        Route::put('/{goalId}', [GoalPageController::class, 'update'])->name('update');
        Route::delete('/{goalId}', [GoalPageController::class, 'destroy'])->name('destroy');
    });
});
