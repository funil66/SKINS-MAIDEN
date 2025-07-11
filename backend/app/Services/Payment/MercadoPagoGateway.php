<?php

namespace App\Services\Payment;

use MercadoPago\SDK;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        SDK::setAccessToken(config('services.mercadopago.token'));
    }

    public function createPayment(array $data): array
    {
        $payment = new \MercadoPago\Payment();
        $payment->transaction_amount = $data['amount'];
        $payment->description = $data['description'] ?? 'Payment';
        $payment->payment_method_id = $data['method_id'];
        $payment->payer = [
            'email' => auth()->user()->email,
        ];
        $payment->save();

        return [
            'id' => $payment->id,
            'status' => $payment->status,
        ];
    }

    public function getPaymentStatus(string $gatewayPaymentId): array
    {
        $payment = \MercadoPago\Payment::find_by_id($gatewayPaymentId);
        return [
            'id' => $payment->id,
            'status' => $payment->status,
        ];
    }

    public function refundPayment(string $gatewayPaymentId): array
    {
        $payment = \MercadoPago\Payment::find_by_id($gatewayPaymentId);
        $refund = new \MercadoPago\Refund(['payment_id' => $gatewayPaymentId]);
        $refund->save();

        return [
            'id' => $refund->id,
            'status' => $refund->status,
        ];
    }
}
