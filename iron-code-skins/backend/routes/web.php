<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SteamAuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\KYCController;
use App\Http\Controllers\WebhookController;

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::get('/steam', [SteamAuthController::class, 'redirectToSteam'])->name('auth.steam');
    Route::get('/steam/callback', [SteamAuthController::class, 'handleSteamCallback'])->name('auth.steam.callback');
});

// Transaction routes
Route::prefix('transactions')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
});

// KYC routes
Route::prefix('kyc')->group(function () {
    Route::post('/submit', [KYCController::class, 'submit'])->name('kyc.submit');
    Route::get('/status', [KYCController::class, 'status'])->name('kyc.status');
});

// Webhook routes
Route::prefix('webhooks')->group(function () {
    Route::post('/payment', [WebhookController::class, 'handlePaymentWebhook'])->name('webhooks.payment');
    Route::post('/transaction', [WebhookController::class, 'handleTransactionWebhook'])->name('webhooks.transaction');
});