<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Booking
 */
class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'seats_booked' => $this->seats_booked,
            'status' => $this->status,
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'price_per_seat' => $this->price_per_seat,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'event' => new EventResource($this->whenLoaded('event')),
        ];
    }
}
