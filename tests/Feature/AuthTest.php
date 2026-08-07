<?php

namespace Tests\Feature;

use App\Domain\User\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_token(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Sami Ben Salah',
            'email' => 'sami@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'department' => 'Operations',
            'job_title' => 'Analyst',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'sami@example.com')
            ->assertJsonPath('data.user.department', 'Operations')
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'sami@example.com']);
        $this->assertTrue($this->get('/api/v1/auth/me', ['Authorization' => 'Bearer '.$response->json('data.token')])->status() === 200);
    }

    public function test_registration_password_must_be_confirmed(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Sami',
            'email' => 'sami@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['password']]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_login_with_invalid_credentials_is_rejected(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401)->assertJsonPath('success', false);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }
}