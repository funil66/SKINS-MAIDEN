<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SteamAuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\KYCController;
use App\Http\Controllers\WebhookController;

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/steam', [SteamAuthController::class, 'authenticate']);
});

// Transaction routes
Route::prefix('transactions')->group(function () {
    Route::post('/', [TransactionController::class, 'create']);
    Route::get('/', [TransactionController::class, 'index']);
    Route::get('/{id}', [TransactionController::class, 'show']);
});

// KYC routes
Route::prefix('kyc')->group(function () {
    Route::post('/', [KYCController::class, 'submit']);
    Route::get('/{userId}', [KYCController::class, 'getKYCStatus']);
});

// Webhook routes
Route::prefix('webhooks')->group(function () {
    Route::post('/payment', [WebhookController::class, 'handlePaymentWebhook']);
    Route::post('/transaction', [WebhookController::class, 'handleTransactionWebhook']);
});