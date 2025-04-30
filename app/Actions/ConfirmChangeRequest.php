<?php

namespace App\Actions;

use App\Enums\ChangeRequestStatus as Status;
use App\Models\ChangeRequest;

class ConfirmChangeRequest
{
    public function __invoke(ChangeRequest $changeRequest): void
    {
        $reservation = $changeRequest->reservation;

        $reservation->apply($changeRequest)->save();

        $changeRequest->update(['status' => Status::CONFIRMED]);
    }
}
