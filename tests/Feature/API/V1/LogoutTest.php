<?php

namespace Tests\Feature\API\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user logout.
     */
    public function test_user_can_logout_with_valid_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        $this->assertCount(0, $user->tokens);
    }

    /**
     * Test that unauthenticated users cannot access the logout endpoint.
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        // Try to logout without being authenticated
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }
}
