<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;

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
