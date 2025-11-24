<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminEventStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_event(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $payload = [
            'title' => 'Admin Event',
            'description' => 'Desc',
            'location' => 'Budapest',
            'starts_at' => now()->addDays(3)->toDateTimeString(),
            'ends_at' => now()->addDays(3)->addHours(2)->toDateTimeString(),
            'price' => 1000,
            'capacity' => 50,
        ];

        $response = $this->postJson('/api/admin/events', $payload);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'event' => ['id', 'title']]);

        $this->assertDatabaseHas('events', [
            'title' => 'Admin Event',
            'created_by' => $admin->id,
        ]);
    }
}
