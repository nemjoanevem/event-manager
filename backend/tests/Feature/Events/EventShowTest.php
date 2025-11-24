<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_view_event_details(): void
    {
        $event = Event::factory()->create();

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'event' => ['id', 'title', 'starts_at', 'ends_at', 'capacity', 'available_seats'],
            ])
            ->assertJsonPath('event.id', $event->id);
    }
}
