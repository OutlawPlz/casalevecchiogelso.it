<?php

namespace App\Actions;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;

class ApproveRequest
{
    /**
     * @param Reservation $reservation
     * @return void
     * @throws \Stripe\Exception\ApiErrorException
     * @throws \Throwable
     */
    public function __invoke(Reservation $reservation): void
    {
        throw_if(
            ! $reservation->inStatus(ReservationStatus::QUOTE),
            ValidationException::withMessages(['status' => "Reservations with the \"{$reservation->status->value}\" status cannot be approved."]),
        );

        $stripe = App::make(StripeClient::class);

        $checkoutSession = $stripe->checkout->sessions->create([
            'line_items' => $reservation->order(),
            'customer' => $reservation->user->createAsStripeCustomer(),
            'mode' => 'payment',
            'success_url' => route('reservation.show', [$reservation]),
            'cancel_url' => route('reservation.show', [$reservation]),
            'metadata' => [
                'reservation' => $reservation->ulid,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'reservation' => $reservation->ulid,
                ]
            ],
        ]);

        $reservation->update([
            'status' => ReservationStatus::PENDING,
            'checkout_session' => [
                'id' => $checkoutSession->id,
                'url' => $checkoutSession->url,
                'expires_at' => $checkoutSession->expires_at,
            ],
        ]);

        /** @var ?User $authUser */
        $authUser = Auth::user();

        activity()
            ->performedOn($reservation)
            ->causedBy($authUser)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $authUser?->email,
            ])
            ->log("The host :properties.user has pre-approved the request.");
    }
}
