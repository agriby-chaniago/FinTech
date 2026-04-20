<?php

use App\Http\Controllers\Auth\OidcController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Service3PlanController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/auth/oidc/redirect', [OidcController::class, 'redirect'])
        ->name('oidc.redirect');

    Route::get('/auth/oidc/callback', [OidcController::class, 'callback'])
        ->name('oidc.callback');
});

Route::post('/auth/oidc/logout', [OidcController::class, 'logout'])
    ->name('oidc.logout');

Route::get('/', function () {
    return view('welcome');
});

// Group semua route yang memerlukan autentikasi
Route::middleware('hybrid.web_auth')->group(function (): void {

    // Dashboard route (dengan total pemasukan/pengeluaran)
    Route::get('/dashboard', [TransactionController::class, 'dashboard'])
        ->name('dashboard');

    // Profile user
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Transaksi (CRUD via controller resource)
    Route::resource('transactions', TransactionController::class);

    // Alias untuk halaman riwayat transaksi
    Route::get('/history', [TransactionController::class, 'index'])->name('history.index');

    // Halaman wadah hasil Service 3
    Route::get('/service3/plans', [Service3PlanController::class, 'index'])->name('service3.plans.index');

    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
});
