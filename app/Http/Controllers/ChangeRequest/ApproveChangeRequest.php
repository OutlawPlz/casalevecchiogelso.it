<?php

namespace App\Http\Controllers\ChangeRequest;

use App\Actions\ApproveReservation;
use App\Actions\ChargeGuest;
use App\Actions\RefundGuest;
use App\Enums\ReservationStatus as Status;
use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\ApiErrorException;
use function App\Helpers\refund_factor;

class ApproveChangeRequest extends Controller
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(Request $request, ChangeRequest $changeRequest): void
    {
        $reservation = $changeRequest->reservation;

        if ($reservation->inStatus(Status::QUOTE)) {
            $reservation->apply($changeRequest)->save();

            $changeRequest->update(['status' => Status::COMPLETED]);

            (new ApproveReservation)($reservation);

            return;
        }

        $priceDelta = $changeRequest->toReservation->tot - $reservation->tot;

        if ($priceDelta < 0) {
            $amount = $priceDelta * refund_factor($reservation, $changeRequest->created_at);

            (new RefundGuest)($reservation, (int) $amount);

            $reservation->apply($changeRequest)->save();

            $changeRequest->update(['status' => Status::COMPLETED]);
        }

        if ($priceDelta === 0) {
            $reservation->apply($changeRequest)->save();

            $changeRequest->update(['status' => Status::COMPLETED]);
        }

        if ($priceDelta > 0) {
            $options = [
                'metadata' => [
                    'reservation' => $reservation->ulid,
                    'change_request' => $changeRequest->id,
                ]
            ];

            (new ChargeGuest)($reservation->user, $priceDelta, $options);
        }

        /** @var ?User $authUser */
        $authUser = Auth::user();

        activity()
            ->performedOn($reservation)
            ->causedBy($authUser)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'change_request' => $changeRequest->id,
                'user' => $authUser?->email,
            ])
            ->log("The $authUser->role :properties.user has pre-approved the change request.");
    }
}
