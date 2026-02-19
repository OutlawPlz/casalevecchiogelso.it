<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class MessagePolicy
{
    public function participate(User $user, Reservation $reservation): bool
    {
        return $user->isHost() || $reservation->user()->is($user);
    }
}
