<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AdminEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_sets_creator(): void
    {
        $service = app(AdminEventService::class);

        $admin = User::factory()->admin()->create();

        $event = $service->create($admin, [
            'title' => 'X',
            'description' => null,
            'location' => 'Budapest',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'price' => 0,
            'capacity' => 10,
        ]);

        $this->assertEquals($admin->id, $event->created_by);
    }
}
