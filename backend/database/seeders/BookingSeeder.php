<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('is_admin', false)->get();
        $events = Event::all();

        foreach ($events as $event) {
            $targetBookings = fake()->numberBetween(3, 15);

            $bookedSeats = 0;

            for ($i = 0; $i < $targetBookings; $i++) {

                if ($bookedSeats >= $event->capacity) {
                    break;
                }

                $remaining = $event->capacity - $bookedSeats;
                $seats = fake()->numberBetween(1, min(5, $remaining));

                $user = $users->random();

                $status = fake()->boolean(85) ? 'active' : 'cancelled';
                $cancelledAt = $status === 'cancelled'
                    ? fake()->dateTimeBetween('-5 days', 'now')
                    : null;

                Booking::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'seats_booked' => $seats,
                    'status' => $status,
                    'cancelled_at' => $cancelledAt,
                    'price_per_seat' => $event->price,
                ]);

                if ($status === 'active') {
                    $bookedSeats += $seats;
                }
            }
        }
    }
}
