<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\BookingIndexRequest;
use App\Http\Requests\Booking\BookingStoreRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {
    }

    public function store(BookingStoreRequest $request, Event $event): JsonResponse
    {
        $this->authorize('create', [Booking::class, $event]);

        $booking = $this->bookingService->create(
            $request->user(),
            $event,
            (int) $request->validated('seats_booked')
        );

        return response()->json([
            'message' => __('bookings.create_success'),
            'booking' => new BookingResource($booking),
        ], 201);
    }

    public function index(BookingIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $paginator = $this->bookingService->paginateForUser(
            $request->user(),
            $request->filters()
        );

        return response()->json([
            'message' => __('bookings.list_success'),
            'bookings' => BookingResource::collection($paginator),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('delete', $booking);

        $booking = $this->bookingService->cancel($request->user(), $booking);

        return response()->json([
            'message' => __('bookings.cancel_success'),
            'booking' => new BookingResource($booking),
        ]);
    }
}
