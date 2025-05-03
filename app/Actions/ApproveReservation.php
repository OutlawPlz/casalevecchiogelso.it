<?php

namespace App\Actions;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class ApproveReservation
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(Reservation $reservation): void
    {
        if (! $reservation->inStatus(ReservationStatus::QUOTE)) {
            throw new \RuntimeException("Reservations with the \"{$reservation->status->value}\" status cannot be approved.");
        }

        $checkoutSession = ['expires_at' => now()->addDay()->timestamp];

        if (! $reservation->user->hasPaymentMethod()) {
            $checkoutSession = $this->createSetupIntent($reservation);
        }

        $reservation->update([
            'status' => ReservationStatus::PENDING,
            'checkout_session' => $checkoutSession,
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
    }

    /**
     * @throws ApiErrorException
     */
    protected function createSetupIntent(Reservation $reservation): array
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $checkoutSession = $stripe->checkout->sessions->create([
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

        return [
            'id' => $checkoutSession->id,
            'url' => $checkoutSession->url,
            'expires_at' => $checkoutSession->expires_at,
        ];
    }
}
