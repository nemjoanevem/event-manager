<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_throws_if_not_enough_seats(): void
    {
        $service = app(BookingService::class);

        $user = User::factory()->create();
        $event = Event::factory()->create(['capacity' => 5]);

        Booking::factory()->create([
            'event_id' => $event->id,
            'status' => 'active',
            'seats_booked' => 5,
        ]);

        $this->expectException(ValidationException::class);

        $service->create($user, $event, 1);
    }
}
