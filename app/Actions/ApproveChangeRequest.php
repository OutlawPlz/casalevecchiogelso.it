<?php

namespace App\Actions;

use App\Enums\ChangeRequestStatus;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ApproveChangeRequest
{
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
                'change_request' => $changeRequest->ulid,
                'user' => $authUser?->email,
            ])
            ->log("The $authUser?->role has approved the change request.");
    }
}
