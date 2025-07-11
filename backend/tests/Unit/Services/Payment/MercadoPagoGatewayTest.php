<?php

namespace Tests\Unit\Services\Payment;

use Tests\TestCase;
use App\Services\Payment\MercadoPagoGateway;
use App\Services\Payment\PaymentGatewayInterface;
use MercadoPago\SDK;

class MercadoPagoGatewayTest extends TestCase
{
    private MercadoPagoGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new MercadoPagoGateway();
    }

    public function test_implements_payment_gateway_interface()
    {
        $this->assertInstanceOf(PaymentGatewayInterface::class, $this->gateway);
    }

    public function test_create_payment_returns_expected_structure()
    {
        $data = [
            'amount' => 100.00,
            'description' => 'Test payment',
            'method_id' => 'pix'
        ];

        $result = $this->gateway->createPayment($data);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertNotEmpty($result['id']);
    }

    public function test_get_payment_status_returns_expected_structure()
    {
        $result = $this->gateway->getPaymentStatus('test_payment_id');

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('status', $result);
    }

    public function test_refund_payment_returns_expected_structure()
    {
        $result = $this->gateway->refundPayment('test_payment_id');

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('status', $result);
    }
}
