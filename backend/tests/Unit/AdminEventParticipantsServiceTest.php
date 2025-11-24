<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Services\AdminEventParticipantsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventParticipantsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginate_returns_only_active_bookings_for_event(): void
    {
        $service = app(AdminEventParticipantsService::class);

        $event = Event::factory()->create();
        $otherEvent = Event::factory()->create();

        $user = User::factory()->create();

        // Active booking for target event
        Booking::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'active',
            'seats_booked' => 2,
        ]);

        // Cancelled booking for target event (should be excluded)
        Booking::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'cancelled',
            'seats_booked' => 1,
        ]);

        // Active booking for different event (should be excluded)
        Booking::factory()->create([
            'event_id' => $otherEvent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'seats_booked' => 5,
        ]);

        $paginator = $service->paginate($event, [
            'page' => 1,
            'per_page' => 25,
            'search' => null,
            'sort_by' => 'created_at',
            'sort_dir' => 'desc',
        ]);

        $this->assertEquals(1, $paginator->total());
        $this->assertEquals(2, $paginator->items()[0]->seats_booked);
        $this->assertTrue($paginator->items()[0]->relationLoaded('user'));
    }
}
