<?php

namespace App\Actions;

use App\Enums\ReservationStatus as Status;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use function App\Helpers\get_overnight_stay;
use function App\Helpers\refund_factor;

class ApproveChangeRequest
{
    /**
     * @throws ApiErrorException
     * @throws ValidationException
     */
    public function __invoke(ChangeRequest $changeRequest):void
    {
        $reservation = $changeRequest->reservation;
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        if ($reservation->inStatus(Status::QUOTE)) {
            $reservation->apply($changeRequest)->save();

            $changeRequest->update(['status' => Status::COMPLETED]);

            (new ApproveReservation)($reservation);

            return;
        }

        $deltaNights = $changeRequest->nights - $reservation->nights;

        $priceDelta = $changeRequest->tot - $reservation->tot;

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

        if ($deltaNights < 0) {
            $overnightStay = get_overnight_stay($reservation->price_list);

            $amount = refund_factor($reservation) * ($deltaNights * $overnightStay['unit_amount']);

            if ($amount) (new RefundGuest)($reservation, (int) $amount);

            $reservation->apply($changeRequest)->push();

            return;
        }

        $checkoutSession = $stripe->checkout->sessions->create([
            'line_items' => [[
                'price' => config('reservation.overnight_stay'),
                'quantity' => $deltaNights,
            ]],
            'customer' => $reservation->user->createAsStripeCustomer(),
            'mode' => 'payment',
            'success_url' => route('reservation.show', [$reservation]),
            'cancel_url' => route('reservation.show', [$reservation]),
            'metadata' => [
                'reservation' => $reservation->ulid,
                'change_request' => $changeRequest->id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'reservation' => $reservation->ulid,
                    'change_request' => $changeRequest->id,
                ]
            ],
        ]);

        $changeRequest->update([
            'status' => Status::PENDING,
            'checkout_session' => [
                'id' => $checkoutSession->id,
                'url' => $checkoutSession->url,
                'expires_at' => $checkoutSession->expires_at,
            ],
        ]);
    }
}
