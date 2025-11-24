<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;

class BookingPolicy
{
    /**
     * Any authenticated user may create a booking.
     */
    public function create(User $user, Event $event): bool
    {
        return true;
    }

    /**
     * User may list only their own bookings.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * User may cancel only their own booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }
}
