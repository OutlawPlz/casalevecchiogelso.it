<?php

namespace App\Actions;

use App\Enums\ChangeRequestStatus;
use App\Enums\ReservationStatus as Status;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\ApiErrorException;

class ApproveChangeRequest
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(ChangeRequest $changeRequest): void
    {
        $reservation = $changeRequest->reservation;
        /** @var ?User $authUser */
        $authUser = Auth::user();

        $reservation->apply($changeRequest)->save();

        $changeRequest->update(['status' => ChangeRequestStatus::APPROVED]);

        activity()
            ->performedOn($reservation)
            ->causedBy($authUser)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'change_request' => $changeRequest->id,
                'user' => $authUser?->email,
            ])
            ->log("The $authUser->role has approved the change request.");

        if ($reservation->inStatus(Status::QUOTE)) {
            (new ApproveReservation)($reservation);
        }
    }
}
