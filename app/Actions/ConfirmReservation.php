<?php

namespace App\Actions;

use App\Enums\ReservationStatus as Status;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ConfirmReservation
{
    public function __invoke(Reservation $reservation, ?User $user = null): void
    {
        $reservation->update(['status' => Status::CONFIRMED]);

        // TODO: Notify the guest and the host.

        /** @var ?User $user */
        $user ??= Auth::user();

        activity()
            ->performedOn($reservation)
            ->causedBy($user)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $user?->email,
            ])
            ->log('The reservation has been confirmed.');
    }
}
