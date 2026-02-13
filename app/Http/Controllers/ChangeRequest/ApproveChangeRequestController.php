<?php

namespace App\Http\Controllers\ChangeRequest;

use App\Actions\ApproveChangeRequest as Approve;
use App\Actions\ApproveReservation;
use App\Enums\ReservationStatus as Status;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessChangeRequestCharge;
use App\Jobs\ProcessChangeRequestRefund;
use App\Models\ChangeRequest;
use App\Models\Reservation;
use Illuminate\Http\Request;

use function App\Helpers\refund_factor;

class ApproveChangeRequestController extends Controller
{
    public function __invoke(Request $request, Reservation $reservation, ChangeRequest $changeRequest): void
    {
        $priceDifference = $changeRequest->priceDifference();

        if ($reservation->inStatus(Status::QUOTE)) {
            $priceDifference = 0;
        }

        if ($priceDifference > 0) {
            ProcessChangeRequestCharge::dispatch(
                $reservation->user,
                $priceDifference,
                $changeRequest
            );
        }

        if ($priceDifference < 0) {
            $amount = $priceDifference * refund_factor($reservation, $changeRequest->created_at);

            ProcessChangeRequestRefund::dispatch($changeRequest, (int) $amount);

            return;
        }

        (new Approve)($changeRequest);

        if ($reservation->inStatus(Status::QUOTE)) {
            (new ApproveReservation)($reservation);
        }
    }
}
