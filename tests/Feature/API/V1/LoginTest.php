<?php

namespace Tests\Feature\API\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user login.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                    'token',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Logged in successfully',
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid login credentials',
            ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid login credentials',
            ]);
    }

    /**
     * Test login validation errors.
     */
    public function test_login_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation Error',
            ])
            ->assertJsonStructure([
                'data' => ['email', 'password'],
            ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'data' => ['email'],
            ]);
    }
}
