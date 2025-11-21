<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AuthService::class);
    }

    public function test_register_creates_user_and_logs_in(): void
    {
        $resource = $this->service->register([
            'name' => 'Unit User',
            'email' => 'unit@example.com',
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'unit@example.com',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals('unit@example.com', $resource->email);
    }

    public function test_login_returns_user_resource_on_success(): void
    {
        $user = User::factory()->create([
            'email' => 'unit@example.com',
            'password' => Hash::make('password123'),
        ]);

        $resource = $this->service->login([
            'email' => 'unit@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertEquals($user->id, $resource->id);
    }

    public function test_login_throws_validation_exception_on_failure(): void
    {
        User::factory()->create([
            'email' => 'unit@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->expectException(ValidationException::class);

        $this->service->login([
            'email' => 'unit@example.com',
            'password' => 'wrong',
        ]);

        $this->assertGuest();
    }

    public function test_logout_logs_out_user(): void
    {
        $user = User::factory()->create();
        $this->be($user);

        $this->assertAuthenticated();

        $this->service->logout($user);

        $this->assertGuest();
    }

    public function test_me_returns_resource_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $resource = $this->service->me($user);

        $this->assertEquals($user->id, $resource->id);
    }

    public function test_me_throws_when_user_is_null(): void
    {
        $this->expectException(\Illuminate\Auth\AuthenticationException::class);

        $this->service->me(null);
    }
}
