<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventService
{
    /**
     * List events with pagination, search and sorting.
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Event::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $query->orderBy($filters['sort_by'], $filters['sort_dir']);

        return $query->paginate(
            perPage: $filters['per_page'],
            page: $filters['page']
        );
    }

    /**
     * Get a single event.
     */
    public function get(Event $event): Event
    {
        return $event;
    }
}
