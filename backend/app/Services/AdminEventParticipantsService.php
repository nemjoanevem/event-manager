<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminEventParticipantsService
{
    /**
     * Paginated participants list for datatable.
     */
    public function paginate(Event $event, array $filters): LengthAwarePaginator
    {
        $query = Booking::query()
            ->where('event_id', $event->id)
            ->where('status', 'active')
            ->with('user');

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sorting
        if (in_array($filters['sort_by'], ['name', 'email'], true)) {
            $query->join('users', 'users.id', '=', 'bookings.user_id')
                ->orderBy("users.{$filters['sort_by']}", $filters['sort_dir'])
                ->select('bookings.*');
        } else {
            $query->orderBy($filters['sort_by'], $filters['sort_dir']);
        }

        return $query->paginate(
            perPage: $filters['per_page'],
            page: $filters['page']
        );
    }

    /**
     * Full participants list for export (no pagination).
     */
    public function all(Event $event, array $filters): Collection
    {
        $filters['page'] = 1;
        $filters['per_page'] = 1000000; // effectively all rows

        return $this->paginate($event, $filters)->getCollection();
    }
}
