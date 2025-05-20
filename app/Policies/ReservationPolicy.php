<?php

namespace App\Policies;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->isHost() || $reservation->user()->is($user);
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        return $user->isHost() || $reservation->user()->is($user);
    }
}
