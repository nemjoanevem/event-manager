<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminEventUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_event(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $event = Event::factory()->create([
            'title' => 'Old',
        ]);

        $response = $this->putJson("/api/admin/events/{$event->id}", [
            'title' => 'New',
        ]);

        $response->assertOk()
            ->assertJsonPath('event.title', 'New');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'New',
        ]);
    }
}
