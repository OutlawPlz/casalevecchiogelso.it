<?php

namespace App\Policies;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * @param  User  $user
     * @param  Reservation  $reservation
     * @return bool
     */
    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->isHost()) return true;

        return $reservation->user()->is($user);
    }

    /**
     * @param  User  $user
     * @return bool
     */
    public function update(User $user): bool
    {
        return $user->isHost();
    }

    /**
     * @param User $user
     * @param Reservation $reservation
     * @return bool
     */
    public function destroy(User $user, Reservation $reservation): bool
    {
        if (! $reservation->inStatus(ReservationStatus::CONFIRMED)) {
            return false;
        }

        return $user->isHost() || $reservation->user()->is($user);
    }
}
