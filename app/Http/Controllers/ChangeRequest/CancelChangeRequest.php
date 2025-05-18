<?php

namespace App\Http\Controllers\ChangeRequest;

use App\Enums\ChangeRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

class CancelChangeRequest extends Controller
{
    public function __invoke(Request $request, Reservation $reservation, ChangeRequest $changeRequest): void
    {
        $changeRequest->update(['status' => ChangeRequestStatus::CANCELLED]);

        /** @var ?User $authUser */
        $authUser = $request->user();

        activity()
            ->causedBy($authUser)
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'change_request' => $changeRequest->id,
                'user' => $authUser?->email,
            ])
            ->log("The $authUser->role has cancelled the change request.");
    }
}
