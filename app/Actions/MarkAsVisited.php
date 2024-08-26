<?php

namespace App\Actions;

use App\Models\Reservation;
use App\Models\User;

class MarkAsVisited
{
    /**
     * @param  Reservation  $reservation
     * @param  User  $user
     * @return void
     */
    public function __invoke(Reservation $reservation, User $user): void
    {
        $visitedAt = $reservation->visited_at ?? [];

        $visitedAt[$user->email] = now()->toDateTimeString();

        $reservation->update(['visited_at' => $visitedAt]);
    }
}
