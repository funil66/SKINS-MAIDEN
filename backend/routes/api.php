<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Interfaces\Http\Controllers\HealthController;
use App\Http\Controllers\Api\V1\ContractsController;
use App\Http\Controllers\Api\V1\ContractTemplatesController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SteamAuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\ReputationController;
use App\Http\Controllers\Api\V1\ReviewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health Check Routes
Route::get('/health', [HealthController::class, 'check'])->name('health.check');
Route::get('/ping', [HealthController::class, 'ping'])->name('health.ping');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Blockchain Routes
    Route::prefix('blockchain')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\BlockchainController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\V1\BlockchainController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\V1\BlockchainController::class, 'show']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\V1\BlockchainController::class, 'destroy']);
    });
    // Contratos
    Route::prefix('contracts')->group(function () {
        Route::get('/', [ContractsController::class, 'index']);
        Route::post('/', [ContractsController::class, 'store']);
        Route::get('/statistics', [ContractsController::class, 'statistics']);
        Route::get('/{id}', [ContractsController::class, 'show']);
        Route::put('/{id}', [ContractsController::class, 'update']);
        Route::post('/{id}/finalize', [ContractsController::class, 'finalize']);
        Route::post('/{id}/sign', [ContractsController::class, 'sign']);
        Route::get('/{id}/signatures', [ContractsController::class, 'signatures']);
        Route::get('/{contractId}/signatures/{signatureId}/verify', [ContractsController::class, 'verifySignature']);
        Route::post('/{contractId}/signatures/{signatureId}/invalidate', [ContractsController::class, 'invalidateSignature']);
    });

    // Templates de Contrato
    Route::prefix('contract-templates')->group(function () {
        Route::get('/', [ContractTemplatesController::class, 'index']);
        Route::post('/', [ContractTemplatesController::class, 'store']);
        Route::get('/active', [ContractTemplatesController::class, 'active']);
        Route::get('/category/{category}', [ContractTemplatesController::class, 'byCategory']);
        Route::get('/{id}', [ContractTemplatesController::class, 'show']);
        Route::put('/{id}', [ContractTemplatesController::class, 'update']);
    });

    // Auth Routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

    // Steam Auth Routes
    Route::get('/auth/steam', [SteamAuthController::class, 'redirectToSteam'])->name('auth.steam');
    Route::get('/auth/steam/callback', [SteamAuthController::class, 'handleSteamCallback']);

    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);

    // My Transactions Route
    Route::get('/my-transactions', [ContractsController::class, 'myTransactions']);

    // Chat Routes
    Route::prefix('chat')->group(function () {
        Route::get('/contracts/{contractId}/messages', [ChatController::class, 'fetchMessages']);
        Route::post('/contracts/{contractId}/messages', [ChatController::class, 'sendMessage']);
        Route::get('/unread-counts', [ChatController::class, 'getUnreadCounts']);
    });
    
    // Payment Routes
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('/{id}', [PaymentController::class, 'show']);
        Route::post('{id}/confirm', [PaymentController::class, 'confirm']);
        Route::delete('/{id}', [PaymentController::class, 'destroy']);
    });

    // Payment Method Routes
    Route::prefix('payment-methods')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index']);
        Route::post('/', [PaymentMethodController::class, 'store']);
        Route::delete('/{id}', [PaymentMethodController::class, 'destroy']);
    });

    // Webhook for Payments
    Route::post('webhooks/payments', [PaymentWebhookController::class, 'store']);

    // Transaction History Routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{id}', [TransactionController::class, 'show']);
    });
    
    // Privacy & LGPD Routes
    Route::prefix('privacy')->group(function () {
        Route::get('/requests', [\App\Http\Controllers\Api\V1\PrivacyController::class, 'index']);
        Route::post('/requests', [\App\Http\Controllers\Api\V1\PrivacyController::class, 'store']);
        Route::get('/requests/{id}', [\App\Http\Controllers\Api\V1\PrivacyController::class, 'show']);
        Route::get('/policy', [\App\Http\Controllers\Api\V1\PrivacyController::class, 'policy']);
        Route::get('/export-data', [\App\Http\Controllers\Api\V1\PrivacyController::class, 'exportData']);
        Route::post('/request-deletion', [\App\Http\Controllers\Api\V1\PrivacyController::class, 'requestDeletion']);
    });
});
