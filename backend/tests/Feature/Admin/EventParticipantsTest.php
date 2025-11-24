<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventParticipantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_event_participants(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $event = Event::factory()->create();

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        Booking::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'seats_booked' => 2,
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/admin/events/{$event->id}/participants");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'participants',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $participants = $response->json('participants');
        if (is_array($participants) && array_key_exists('data', $participants)) {
            $participants = $participants['data'];
        }

        $this->assertCount(1, $participants);
        $this->assertEquals('John Doe', $participants[0]['name']);
        $this->assertEquals('john@example.com', $participants[0]['email']);
        $this->assertEquals(2, $participants[0]['seats_booked']);
    }

    public function test_non_admin_cannot_list_event_participants(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);
        Sanctum::actingAs($user);

        $event = Event::factory()->create();

        $response = $this->getJson("/api/admin/events/{$event->id}/participants");

        $response->assertForbidden();
    }

    public function test_admin_can_export_participants_as_csv(): void
    {
        $this->app->setLocale('en');

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $event = Event::factory()->create();

        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        Booking::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'seats_booked' => 3,
            'status' => 'active',
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get("/api/admin/events/{$event->id}/participants/export");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = method_exists($response, 'streamedContent')
            ? $response->streamedContent()
            : $response->getContent();

        // Build expected translated header
        $expectedHeader = implode(',', [
            __('events.Name'),
            __('events.Email'),
            __('events.Seats booked'),
            __('events.Booked at'),
        ]);

        // fputcsv may quote fields that contain spaces, so allow optional quotes
        $pattern = '/^' . preg_quote(__('events.Name'), '/') .
            ',' . preg_quote(__('events.Email'), '/') .
            ',"?' . preg_quote(__('events.Seats booked'), '/') . '"?' .
            ',"?' . preg_quote(__('events.Booked at'), '/') . '"?\s/m';

        $this->assertMatchesRegularExpression($pattern, $content);

        // Data row checks
        $this->assertStringContainsString('Jane Doe', $content);
        $this->assertStringContainsString('jane@example.com', $content);
        $this->assertStringContainsString('3', $content);
    }
}
