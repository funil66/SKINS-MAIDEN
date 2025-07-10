<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Http\Requests\TransactionRequest;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Store a new transaction.
     *
     * @param TransactionRequest $request
     * @return JsonResponse
     */
    public function store(TransactionRequest $request): JsonResponse
    {
        $transaction = Transaction::create($request->validated());

        // Process payment through the payment service
        $this->paymentService->processPayment($transaction);

        return response()->json([
            'message' => 'Transaction created successfully.',
            'transaction' => $transaction,
        ], 201);
    }

    /**
     * Show the specified transaction.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $transaction = Transaction::findOrFail($id);

        return response()->json($transaction);
    }

    /**
     * Update the specified transaction.
     *
     * @param TransactionRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(TransactionRequest $request, int $id): JsonResponse
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->validated());

        return response()->json([
            'message' => 'Transaction updated successfully.',
            'transaction' => $transaction,
        ]);
    }

    /**
     * Remove the specified transaction.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully.',
        ]);
    }
}