<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AdminEventService
{
    /**
     * Create a new event.
     */
    public function create(User $user, array $data): Event
    {
        return Event::create([
            'created_by' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'location' => $data['location'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'price' => $data['price'],
            'capacity' => $data['capacity'],
        ]);
    }

    /**
     * Update an existing event.
     */
    public function update(Event $event, array $data): Event
    {
        if (isset($data['capacity']) && $data['capacity'] < $event->activeBookings()->sum('seats_booked')) {
            throw ValidationException::withMessages([
                'capacity' => [__('events.capacity_too_low')],
            ]);
        }

        $event->update($data);

        return $event->refresh();
    }

    /**
     * Delete an event.
     */
    public function delete(Event $event): void
    {
        $event->delete();
    }
}
