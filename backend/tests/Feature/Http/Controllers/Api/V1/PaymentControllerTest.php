<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\Payment;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_user_can_list_their_payments()
    {
        Payment::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/payments');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_user_can_create_payment()
    {
        $contract = Contract::factory()->create();
        
        $data = [
            'contract_id' => $contract->id,
            'amount' => 100.00,
            'currency' => 'BRL',
            'method_id' => 'pix',
        ];

        $response = $this->postJson('/api/v1/payments', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'contract_id',
                'amount',
                'currency',
                'status'
            ]);
    }

    public function test_user_can_view_payment_status()
    {
        $payment = Payment::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'status',
            ]);
    }

    public function test_user_cannot_access_other_users_payments()
    {
        $otherUser = User::factory()->create();
        $payment = Payment::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/v1/payments/{$payment->id}");

        $response->assertStatus(404);
    }
}
