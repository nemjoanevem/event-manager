<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'seats_booked',
        'status',
        'cancelled_at',
        'price_per_seat',
    ];

    protected function casts(): array
    {
        return [
            'seats_booked'   => 'integer',
            'price_per_seat' => 'integer',
            'cancelled_at'   => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
