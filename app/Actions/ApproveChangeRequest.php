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
use function App\Helpers\is_overnight_stay;
use function App\Helpers\refund_amount;

class ApproveChangeRequest
{
    /**
     * @throws ApiErrorException
     * @throws ValidationException
     */
    public function __invoke(ChangeRequest $changeRequest):void
    {
        $reservation = $changeRequest->reservation;

        $deltaNights = $changeRequest->nights - $reservation->nights;

        if ($deltaNights === 0 || $reservation->inStatus(Status::QUOTE, Status::PENDING)) {
            $reservation
                ->fill(['status' => Status::QUOTE])
                ->apply($changeRequest)
                ->push();

            (new ApproveReservation)($reservation);

            return;
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

        if ($deltaNights < 0) {
            $overnightStay = array_find(
                $reservation->price_list,
                fn ($line) => is_overnight_stay($line['product'])
            );

            $amount = refund_amount($reservation, tot: $deltaNights * $overnightStay['unit_amount']);

            if ($amount) (new RefundGuest)($reservation, $amount);

            $reservation->apply($changeRequest)->push();

            return;
        }

        $stripe = App::make(StripeClient::class);

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
