<?php

namespace App\Actions;

use App\Enums\ReservationStatus;
use App\Models\ChangeRequest;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

class ApproveChangeRequest
{
    /**
     * @throws ApiErrorException
     * @throws ValidationException
     */
    public function __invoke(ChangeRequest $changeRequest):void
    {
        $reservation = $changeRequest->reservation;

        if ($reservation->inStatus(ReservationStatus::QUOTE, ReservationStatus::PENDING)) {
            $reservation
                ->fill(['status' => ReservationStatus::QUOTE])
                ->apply($changeRequest)
                ->push();

            (new ApproveReservation)($reservation);

            return;
        }

        // TODO: Handle reservation confirmed case.
    }
}
