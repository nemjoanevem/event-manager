<?php

namespace Tests\Feature\Bookings;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_book_seats_for_event(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $event = Event::factory()->create([
            'capacity' => 10,
        ]);

        $response = $this->postJson("/api/events/{$event->id}/bookings", [
            'seats_booked' => 3,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'booking' => ['id', 'event_id', 'user_id', 'seats_booked', 'status'],
            ]);

        $this->assertDatabaseHas('bookings', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'seats_booked' => 3,
            'status' => 'active',
        ]);
    }

    public function test_user_can_list_own_bookings(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $event = Event::factory()->create();
        Booking::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $response = $this->getJson('/api/bookings');

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'bookings',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $bookings = $response->json('bookings');

        if (is_array($bookings) && array_key_exists('data', $bookings)) {
            $bookings = $bookings['data'];
        }

        $this->assertCount(1, $bookings);
    }

    public function test_user_can_cancel_own_booking_before_start(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $event = Event::factory()->create([
            'starts_at' => now()->addDays(2),
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'active',
        ]);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertOk()
            ->assertJsonPath('booking.status', 'cancelled');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }
}
