<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Any user may list events.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any user may view an event.
     */
    public function view(User $user, Event $event): bool
    {
        return true;
    }
}
