<?php

namespace Tests\Feature\API\V1;

use Tests\TestCase;

class PingTest extends TestCase
{
    /**
     * Test if the API is up and running.
     */
    public function test_ping_endpoint_returns_success_response(): void
    {
        $response = $this->getJson('/api/v1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'timestamp',
                    'version',
                    'health'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'API is up and running',
                'data' => [
                    'health' => 'ok',
                    'version' => '1.0.0'
                ]
            ]);
    }
}
