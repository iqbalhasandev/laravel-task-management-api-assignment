<?php

namespace Tests\Feature\API\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user registration.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

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
                'message' => 'User registered successfully',
                'data' => [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }

    /**
     * Test validation errors during registration.
     */
    public function test_registration_validation_errors(): void
    {
        // Test with missing fields
        $response = $this->postJson('/api/v1/register', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation Error',
            ])
            ->assertJsonStructure([
                'data' => ['name', 'email', 'password'],
            ]);

        // Test with invalid email
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'data' => ['email'],
            ]);

        // Test with mismatched passwords
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'data' => ['password'],
            ]);
    }

    /**
     * Test duplicate email registration.
     */
    public function test_user_cannot_register_with_duplicate_email(): void
    {
        // Create a user first
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        // Try to register with the same email
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation Error',
            ])
            ->assertJsonStructure([
                'data' => ['email'],
            ]);
    }
}
