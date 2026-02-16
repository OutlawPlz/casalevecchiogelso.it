<?php

namespace App\Http\Controllers\Reservation;

use App\Enums\ReservationStatus as Status;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RejectReservationController extends Controller
{
    public function __invoke(Request $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update(['status' => Status::REJECTED]);

        $authUser = $request->user();

        activity()
            ->performedOn($reservation)
            ->causedBy($authUser)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $authUser?->email,
            ])
            ->log("The $authUser?->role rejected the reservation.");

        return redirect()->back();
    }
}
