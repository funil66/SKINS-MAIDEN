<?php

namespace Tests\Unit\Services\Payment;

use Tests\TestCase;
use App\Services\Payment\AntiFraudService;

class AntiFraudServiceTest extends TestCase
{
    private AntiFraudService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AntiFraudService();
    }

    public function test_score_transaction_returns_float()
    {
        $data = [
            'ip' => '127.0.0.1',
            'amount' => 100.00
        ];

        $result = $this->service->scoreTransaction($data);

        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0.0, $result);
        $this->assertLessThanOrEqual(1.0, $result);
    }

    public function test_high_amount_increases_risk_score()
    {
        $lowData = [
            'amount' => 100.00
        ];

        $highData = [
            'amount' => 15000.00
        ];

        $lowScore = $this->service->scoreTransaction($lowData);
        $highScore = $this->service->scoreTransaction($highData);

        $this->assertGreaterThan($lowScore, $highScore);
    }
}
