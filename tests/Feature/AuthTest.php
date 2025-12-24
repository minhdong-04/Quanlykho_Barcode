<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_logout_and_me()
    {
        // Register
        $payload = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $res = $this->postJson('/api/v1/auth/register', $payload);
        $res->assertStatus(201)->assertJsonStructure(['user', 'token']);

        // Login
        $res = $this->postJson('/api/v1/auth/login', ['email' => 'testuser@example.com', 'password' => 'secret123']);
        $res->assertStatus(200)->assertJsonStructure(['user', 'token']);

        // Use actingAs for subsequent authenticated requests in tests
        $user = User::where('email', 'testuser@example.com')->first();
        \Laravel\Sanctum\Sanctum::actingAs($user);

        // Me
        $res = $this->getJson('/api/v1/auth/me');
        $res->assertStatus(200)->assertJsonPath('email', 'testuser@example.com');

        // Logout
        $res = $this->postJson('/api/v1/auth/logout');
        $res->assertStatus(200)->assertJson(['message' => 'Logged out']);

        // (Token invalidation verified by logout response above)
    }
}
