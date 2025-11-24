<?php

namespace App\Providers;

use App\Models\Event;
use App\Policies\EventPolicy;
use App\Models\Booking;
use App\Policies\BookingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Event::class => EventPolicy::class,
        Booking::class => BookingPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
