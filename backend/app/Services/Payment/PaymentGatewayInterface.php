<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a new payment via gateway.
     *
     * @param array $data
     * @return array Response data including gateway payment id and status
     */
    public function createPayment(array $data): array;

    /**
     * Retrieve payment status from gateway.
     *
     * @param string $gatewayPaymentId
     * @return array Response data with current status
     */
    public function getPaymentStatus(string $gatewayPaymentId): array;

    /**
     * Refund an existing payment via gateway.
     *
     * @param string $gatewayPaymentId
     * @return array Response data for refund
     */
    public function refundPayment(string $gatewayPaymentId): array;
}
