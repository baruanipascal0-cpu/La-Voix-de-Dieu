<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_mobile_payload(): void
    {
        $response = $this->postJson('/api/v1/auth/register/', [
            'name' => 'Jean Kindu',
            'email' => 'jean@example.com',
            'phone' => '0 891 500 917',
            'password' => 'password123',
            'password_confirm' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.email', 'jean@example.com')
            ->assertJsonPath('user.phone', '+243891500917')
            ->assertJsonStructure([
                'access_token',
                'refresh_token',
                'user' => ['id', 'name', 'email', 'phone', 'role'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jean@example.com',
            'phone' => '+243891500917',
        ]);
    }

    public function test_user_can_login_and_get_current_profile(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'phone' => '+243891500918',
            'password' => 'password123',
        ]);

        $login = $this->postJson('/api/v1/auth/login/', [
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk()->assertJsonPath('user.phone', '+243891500918');

        $token = $login->json('access_token');

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me/')
            ->assertOk()
            ->assertJsonPath('user.email', 'member@example.com');
    }

    public function test_refresh_token_rotates_mobile_tokens(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $login = $this->postJson('/api/v1/auth/login/', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $refreshToken = $login->json('refresh_token');

        $this->postJson('/api/v1/auth/refresh/', [
            'refresh_token' => $refreshToken,
        ])
            ->assertOk()
            ->assertJsonStructure(['access_token', 'refresh_token', 'user']);
    }

    public function test_refresh_token_cannot_access_protected_mobile_routes(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $login = $this->postJson('/api/v1/auth/login/', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $refreshToken = $login->json('refresh_token');

        $this
            ->withHeader('Authorization', 'Bearer '.$refreshToken)
            ->getJson('/api/v1/auth/me/')
            ->assertForbidden();
    }

    public function test_authenticated_user_can_store_device_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-mobile')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/device-token/', [
                'token' => 'fcm-token-123',
                'platform' => 'android',
                'device_id' => 'pixel-test',
                'app_version' => '1.0.0',
            ])
            ->assertOk()
            ->assertJsonPath('device_token.platform', 'android');

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $user->id,
            'token' => 'fcm-token-123',
            'platform' => 'android',
        ]);
    }
}
