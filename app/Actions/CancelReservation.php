<?php

namespace App\Actions;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use function App\Helpers\refund_factor;

class CancelReservation
{
    /**
     * @throws ApiErrorException
     * @throws ValidationException
     */
    public function __invoke(Reservation $reservation, string $reason, ?User $causer = null): void
    {
        $refundFactor = refund_factor($reservation, causer: $causer);

        $daysLeft = date_diff(now(), $reservation->check_out)->d;

        $refundAmount = $reservation->amountPaid() * $refundFactor * ($daysLeft / $reservation->nights);

        if ($refundAmount) (new Refund)($reservation->payments, (int) $refundAmount);

        $reservation->update(['status' => ReservationStatus::CANCELLED]);

        activity()
            ->performedOn($reservation)
            ->causedBy($causer)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $causer?->email,
                'message' => $reason,
            ])
            ->log("The $causer?->role cancelled the reservation.");
    }
}
