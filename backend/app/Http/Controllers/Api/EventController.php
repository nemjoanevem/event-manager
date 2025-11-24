<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\EventIndexRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $eventService
    ) {
    }

    public function index(EventIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Event::class);

        $paginator = $this->eventService->paginate($request->filters());

        return response()->json([
            'message' => __('events.list_success'),
            'events' => EventResource::collection($paginator),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(EventShowRequest $request, Event $event): JsonResponse
    {
        // public show, no auth required
        $event = $this->eventService->get($event);

        return response()->json([
            'message' => __('events.show_success'),
            'event' => new EventResource($event),
        ]);
    }
}
