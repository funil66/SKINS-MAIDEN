<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'contract_id' => Contract::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => 'BRL',
            'payment_gateway' => 'mercadopago',
            'gateway_payment_id' => $this->faker->uuid,
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'metadata' => ['test' => true],
        ];
    }
}
