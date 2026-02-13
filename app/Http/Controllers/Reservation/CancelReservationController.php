<?php

namespace App\Http\Controllers\Reservation;

use App\Actions\CancelReservation;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use function App\Helpers\refund_factor;

class CancelReservationController
{
    public function show(Request $request, Reservation $reservation): View
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $refundAmount = (int) ($reservation->amountPaid() * refund_factor($reservation, causer: $authUser));

        return view('reservation.delete', [
            'authUser' => $authUser,
            'reservation' => $reservation,
            'refundAmount' => $refundAmount,
        ]);
    }

    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        $attributes = $request->validate(self::rules());

        (new CancelReservation)($reservation, $attributes['reason'], $request->user());

        return redirect()->route('reservation.show', [$reservation]);
    }

    public static function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
