<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\EventStoreRequest;
use App\Http\Requests\Event\EventUpdateRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\AdminEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminEventController extends Controller
{
    public function __construct(
        private readonly AdminEventService $adminEventService
    ) {
    }

    public function store(EventStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        $event = $this->adminEventService->create(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => __('events.create_success'),
            'event' => new EventResource($event),
        ], 201);
    }

    public function update(EventUpdateRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $event = $this->adminEventService->update($event, $request->validated());

        return response()->json([
            'message' => __('events.update_success'),
            'event' => new EventResource($event),
        ]);
    }

    public function destroy(Request $request, Event $event): JsonResponse
    {
        $this->authorize('delete', $event);

        $this->adminEventService->delete($event);

        return response()->json([
            'message' => __('events.delete_success'),
        ]);
    }
}
