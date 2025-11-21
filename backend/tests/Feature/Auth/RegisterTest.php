<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $this->withoutMiddleware([
            EnsureFrontendRequestsAreStateful::class,
            VerifyCsrfToken::class,
        ]);

        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'is_admin', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'is_admin' => 0,
        ]);

        $this->assertAuthenticated('web');
    }

    public function test_register_requires_valid_data(): void
    {
        $this->withoutMiddleware([
            EnsureFrontendRequestsAreStateful::class,
            VerifyCsrfToken::class,
        ]);

        $response = $this->postJson('/api/register', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_requires_unique_email(): void
    {
        $this->withoutMiddleware([
            EnsureFrontendRequestsAreStateful::class,
            VerifyCsrfToken::class,
        ]);

        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $payload = [
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_password_confirmation(): void
    {
        $this->withoutMiddleware([
            EnsureFrontendRequestsAreStateful::class,
            VerifyCsrfToken::class,
        ]);

        $payload = [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
