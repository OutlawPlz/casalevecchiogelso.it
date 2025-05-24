<?php

namespace App\Actions;

use App\Enums\ChangeRequestStatus;
use App\Models\ChangeRequest;
use App\Models\User;

class CancelChangeRequest
{
    public function __invoke(ChangeRequest $changeRequest, ?User $causer = null): void
    {
        $changeRequest->update(['status' => ChangeRequestStatus::CANCELLED]);

        activity()
            ->performedOn($changeRequest->reservation)
            ->causedBy($causer)
            ->withProperties([
                'reservation' => $changeRequest->reservation->ulid,
                'change_request' => $changeRequest->ulid,
                'user' => $causer?->email,
            ])
            ->log("The $causer?->role has cancelled the change request.");
    }
}
