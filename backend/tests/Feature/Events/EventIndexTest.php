<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_events_with_pagination(): void
    {
        Event::factory()->count(3)->create();

        $response = $this->getJson('/api/events?per_page=2');

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'events',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $events = $response->json('events');

        if (is_array($events) && array_key_exists('data', $events)) {
            $events = $events['data'];
        }

        $this->assertCount(2, $events);
    }
}
