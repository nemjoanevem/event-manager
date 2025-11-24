<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'location',
        'price',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
            'price'     => 'integer',
            'capacity'  => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBookings(): HasMany
    {
        return $this->hasMany(Booking::class)->where('status', 'active');
    }

    public function getAvailableSeatsAttribute(): int
    {
        $activeSum = $this->active_bookings_sum_seats_booked ?? null;

        if ($activeSum === null) {
            $activeSum = $this->activeBookings()->sum('seats_booked');
        }

        return max(0, $this->capacity - (int) $activeSum);
    }
}
