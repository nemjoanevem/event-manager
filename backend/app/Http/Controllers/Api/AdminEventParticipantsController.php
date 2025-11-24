<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventParticipantsIndexRequest;
use App\Http\Resources\EventParticipantResource;
use App\Models\Event;
use App\Services\AdminEventParticipantsService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminEventParticipantsController extends Controller
{
    public function __construct(
        private readonly AdminEventParticipantsService $participantsService
    ) {
    }

    public function index(EventParticipantsIndexRequest $request, Event $event): JsonResponse
    {
        $this->authorize('viewParticipants', $event);

        $paginator = $this->participantsService->paginate($event, $request->filters());

        return response()->json([
            'message' => __('events.participants_list_success'),
            'participants' => EventParticipantResource::collection($paginator),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function export(EventParticipantsIndexRequest $request, Event $event): StreamedResponse
    {
        $this->authorize('viewParticipants', $event);

        $rows = $this->participantsService->all($event, $request->filters());

        $filename = "event-{$event->id}-participants.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Name', 'Email', 'Seats booked', 'Booked at']);

            foreach ($rows as $booking) {
                fputcsv($out, [
                    $booking->user->name,
                    $booking->user->email,
                    $booking->seats_booked,
                    $booking->created_at?->toDateTimeString(),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
