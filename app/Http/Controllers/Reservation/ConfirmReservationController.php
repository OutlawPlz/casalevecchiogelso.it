<?php

namespace App\Http\Controllers\Reservation;

use App\Actions\ConfirmReservation;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConfirmReservationController
{
    public function __invoke(Request $request, Reservation $reservation): RedirectResponse
    {
        if (! $reservation->inStatus(ReservationStatus::PENDING)) abort(403);

        if (array_key_exists('id', $reservation->checkout_session)) {
            return redirect($reservation->checkout_session['url']);
        }

        (new ConfirmReservation)($reservation);

        return redirect()->route('reservation.show', [$reservation]);
    }
}
