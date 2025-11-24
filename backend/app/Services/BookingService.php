<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Create a booking with capacity check.
     *
     * Uses transaction + row lock to avoid race conditions.
     */
    public function create(User $user, Event $event, int $seatsBooked): Booking
    {
        return DB::transaction(function () use ($user, $event, $seatsBooked) {

            $event = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();

            $alreadyBooked = Booking::where('event_id', $event->id)
                ->where('status', 'active')
                ->sum('seats_booked');

            $available = $event->capacity - $alreadyBooked;

            if ($seatsBooked > $available) {
                throw ValidationException::withMessages([
                    'seats_booked' => [__('bookings.not_enough_seats')],
                ]);
            }

            return Booking::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'seats_booked' => $seatsBooked,
                'status' => 'active',
                'price_per_seat' => $event->price,
            ]);
        });
    }

    /**
     * List authenticated user's bookings with pagination.
     */
    public function paginateForUser(User $user, array $filters): LengthAwarePaginator
    {
        $query = Booking::query()
            ->where('user_id', $user->id)
            ->with('event');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy($filters['sort_by'], $filters['sort_dir']);

        return $query->paginate(
            perPage: $filters['per_page'],
            page: $filters['page']
        );
    }

    /**
     * Cancel booking if event has not started yet.
     */
    public function cancel(User $user, Booking $booking): Booking
    {
        if ($booking->status === 'cancelled') {
            return $booking;
        }

        $event = $booking->event()->first();

        if ($event && now()->greaterThanOrEqualTo($event->starts_at)) {
            throw ValidationException::withMessages([
                'booking' => [__('bookings.cannot_cancel_after_start')],
            ]);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $booking;
    }
}
