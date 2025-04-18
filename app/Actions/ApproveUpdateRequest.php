<?php

namespace App\Actions;

use App\Models\UpdateRequest;

class ApproveUpdateRequest
{
    public function __invoke(UpdateRequest $updateRequest): void
    {
        $reservation = $updateRequest->reservation;
    }
}
