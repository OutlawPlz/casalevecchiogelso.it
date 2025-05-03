<?php

namespace App\Http\Controllers\Reservation;

use App\Actions\ApproveReservation;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;

class ApproveReservationController extends Controller
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(Request $request, Reservation $reservation): RedirectResponse
    {
        /** @var ?User $authUser */
        $authUser = $request->user();

        if (! $authUser?->isHost()) abort(403);

        (new ApproveReservation)($reservation);

        return redirect()->back();
    }
}
