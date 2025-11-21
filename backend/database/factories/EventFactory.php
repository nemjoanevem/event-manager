<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 days', '+60 days');
        $end = (clone $start)->modify('+' . fake()->numberBetween(1, 6) . ' hours');

        return [
            'created_by' => User::factory()->admin(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional(0.8)->paragraphs(2, true),
            'starts_at' => $start,
            'ends_at' => $end,
            'location' => fake()->city(),
            'price' => fake()->randomElement([0, 1500, 3000, 4990, 9900]),
            'capacity' => fake()->numberBetween(10, 200),
        ];
    }
}
