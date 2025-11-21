<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'seats_booked' => fake()->numberBetween(1, 5),
            'status' => 'active',
            'cancelled_at' => null,
            'price_per_seat' => null,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(function () {
            $cancelledAt = fake()->dateTimeBetween('-10 days', 'now');

            return [
                'status' => 'cancelled',
                'cancelled_at' => $cancelledAt,
            ];
        });
    }
}
