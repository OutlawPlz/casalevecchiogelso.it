<?php

namespace App\Http\Controllers\ChangeRequest;

use App\Actions\ApproveChangeRequest as Approve;
use App\Actions\Charge;
use App\Actions\Refund;
use App\Enums\ReservationStatus as Status;
use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use function App\Helpers\refund_factor;

class ApproveChangeRequestController extends Controller
{
    /**
     * @throws ApiErrorException
     * @throws ValidationException
     */
    public function __invoke(Request $request, Reservation $reservation, ChangeRequest $changeRequest): void
    {
        $priceDelta = $changeRequest->priceDifference();

        if ($reservation->inStatus(Status::QUOTE)) $priceDelta = 0;

        if ($priceDelta < 0) {
            $amount = $priceDelta * refund_factor($reservation, $changeRequest->created_at);

            (new Refund)($reservation->payments, (int) $amount);

            (new Approve)($changeRequest);
        }

        if ($priceDelta === 0) (new Approve)($changeRequest);

        if ($priceDelta > 0) {
            $options = [
                'metadata' => [
                    'reservation' => $reservation->ulid,
                    'change_request' => $changeRequest->ulid,
                ]
            ];

            (new Charge)($reservation->user, $priceDelta, parameters: $options);
        }
    }
}
