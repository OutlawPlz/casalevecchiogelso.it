<?php

namespace App\Actions;

use App\Enums\ReservationStatus;
use App\Jobs\Refund;
use App\Models\Reservation;
use App\Models\User;

use function App\Helpers\refund_factor;

class CancelReservation
{
    public function __invoke(Reservation $reservation, string $reason, ?User $causer = null): void
    {
        $refundAmount = (int) ($reservation->amountPaid() * refund_factor($reservation, causer: $causer));

        if ($refundAmount) {
            Refund::dispatch($reservation, $refundAmount, ['reservation' => $reservation->ulid]);
        }

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
