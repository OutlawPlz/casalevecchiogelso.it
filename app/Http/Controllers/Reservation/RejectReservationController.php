<?php

namespace App\Http\Controllers\Reservation;

use App\Enums\ReservationStatus as Status;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RejectReservationController extends Controller
{
    public function __invoke(Request $request, Reservation $reservation): RedirectResponse
    {
        /** @var ?User $authUser */
        $authUser = $request->user();

        if (! $authUser?->isHost()) abort(403);

        if (! $reservation->inStatus(Status::QUOTE)) {
            throw new \RuntimeException("Reservations with the \"{$reservation->status->value}\" status cannot be rejected.");
        }

        $reservation->update(['status' => Status::REJECTED]);

        return redirect()->back();
    }
}
