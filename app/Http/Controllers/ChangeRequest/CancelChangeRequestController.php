<?php

namespace App\Http\Controllers\ChangeRequest;

use App\Actions\CancelChangeRequest;
use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

class CancelChangeRequestController extends Controller
{
    public function __invoke(Request $request, Reservation $reservation, ChangeRequest $changeRequest): void
    {
        /** @var ?User $authUser */
        $authUser = $request->user();

        (new CancelChangeRequest)($changeRequest, $authUser);
    }
}
