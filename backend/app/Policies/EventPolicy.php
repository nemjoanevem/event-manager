<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Only admins may create events.
     */
    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Only admins may update events.
     */
    public function update(User $user, Event $event): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Only admins may delete events.
     */
    public function delete(User $user, Event $event): bool
    {
        return (bool) $user->is_admin;
    }
}
