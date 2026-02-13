<?php

namespace App\Actions;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class ApproveReservation
{
    /**
     * @throws ApiErrorException|BindingResolutionException
     */
    public function __invoke(Reservation $reservation): Session
    {
        $checkoutSession = $this->createSetupIntent($reservation);

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
            ->log("The $authUser?->role has pre-approved the request.");

        return $checkoutSession;
    }

    /**
     * @throws ApiErrorException|BindingResolutionException
     */
    protected function createSetupIntent(Reservation $reservation): Session
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        return $stripe->checkout->sessions->create([
            'customer' => $reservation->user->createAsStripeCustomer(),
            'currency' => config('services.stripe.currency'),
            'mode' => 'setup',
            'success_url' => route('reservation.show', [$reservation]),
            'cancel_url' => route('reservation.show', [$reservation]),
            'metadata' => [
                'reservation' => $reservation->ulid,
            ],
            'setup_intent_data' => [
                'metadata' => [
                    'reservation' => $reservation->ulid,
                ]
            ],
        ]);
    }
}
