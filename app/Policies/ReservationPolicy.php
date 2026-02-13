<?php

namespace App\Policies;

use App\Enums\ReservationStatus as Status;
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
        return $reservation->inStatus(Status::CONFIRMED, Status::PENDING)
            && ($user->isHost() || $reservation->user()->is($user));
    }

    public function reject(User $user, Reservation $reservation): bool
    {
        return $user->isHost() && $reservation->inStatus(Status::QUOTE, Status::PENDING);
    }

    public function approve(User $user, Reservation $reservation): bool
    {
        return $user->isHost() && $reservation->inStatus(Status::QUOTE);
    }
}
