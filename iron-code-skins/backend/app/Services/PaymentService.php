<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class PaymentService
{
    protected $paymentGatewayUrl;

    public function __construct()
    {
        $this->paymentGatewayUrl = config('services.payment_gateway.url');
    }

    public function processPayment(User $user, Transaction $transaction)
    {
        // Validate transaction details
        $this->validateTransaction($transaction);

        // Prepare payment request data
        $paymentData = [
            'user_id' => $user->id,
            'amount' => $transaction->amount,
            'currency' => 'BRL', // Assuming Brazilian Real
            'transaction_id' => $transaction->id,
        ];

        // Send payment request to the payment gateway
        $response = Http::post($this->paymentGatewayUrl . '/process', $paymentData);

        if ($response->successful()) {
            // Update transaction status
            $transaction->status = 'completed';
            $transaction->save();
            return $response->json();
        } else {
            // Handle payment failure
            $transaction->status = 'failed';
            $transaction->save();
            throw new \Exception('Payment processing failed: ' . $response->body());
        }
    }

    protected function validateTransaction(Transaction $transaction)
    {
        if ($transaction->amount <= 0) {
            throw new \InvalidArgumentException('Transaction amount must be greater than zero.');
        }

        // Additional validation logic can be added here
    }
}