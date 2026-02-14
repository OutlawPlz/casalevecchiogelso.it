<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Reservation $reservation): bool
    {
        if ($user->isHost()) {
            return true;
        }

        return $reservation->user()->is($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->isHost()) {
            return true;
        }

        return $reservation->user()->is($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Reservation $reservation): bool
    {
        if ($user->isHost()) {
            return true;
        }

        return $reservation->user()->is($user);
    }
}
