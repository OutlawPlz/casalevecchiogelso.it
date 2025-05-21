<?php

namespace App\Http\Controllers\Reservation;

use App\Actions\RefundGuest;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use function App\Helpers\refund_factor;

class CancelReservationController
{
    public function show(Request $request, Reservation $reservation): View
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $refundFactor = refund_factor($reservation, causer: $authUser);

        $daysLeft = date_diff(now(), $reservation->check_out)->d;

        $refundAmount = $reservation->amountPaid() * $refundFactor * ($daysLeft / $reservation->nights);

        return view('reservation.delete', [
            'authUser' => $authUser,
            'reservation' => $reservation,
            'refundAmount' => $refundAmount,
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        $attributes = $request->validate(self::rules());

        /** @var ?User $authUser */
        $authUser = $request->user();

        $refundFactor = refund_factor($reservation, causer: $authUser);

        $daysLeft = date_diff(now(), $reservation->check_out)->d;

        $refundAmount = $reservation->amountPaid() * $refundFactor * ($daysLeft / $reservation->nights);

        if ($refundAmount) (new RefundGuest)($reservation->payments, (int) $refundAmount);

        $reservation->update(['status' => ReservationStatus::CANCELLED]);

        // TODO: Send notification to host and guest.

        activity()
            ->performedOn($reservation)
            ->causedBy($authUser)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $authUser?->email,
                'message' => $attributes['reason'],
            ])
            ->log("The $authUser?->role cancelled the reservation.");

        return redirect()->route('reservation.show', [$reservation]);
    }

    public static function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
