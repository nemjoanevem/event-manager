<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminEventDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_event(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $event = Event::factory()->create();

        $response = $this->deleteJson("/api/admin/events/{$event->id}");

        $response->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }
}
