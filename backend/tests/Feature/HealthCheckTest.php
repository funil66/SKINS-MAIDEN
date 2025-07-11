<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_check_endpoint_returns_success()
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'timestamp',
                     'version',
                     'environment',
                     'checks'
                 ]);
    }

    public function test_ping_endpoint_returns_pong()
    {
        $response = $this->get('/api/ping');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'pong'
                 ]);
    }
}
