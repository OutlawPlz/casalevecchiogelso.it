<?php

namespace App\Actions;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

class RefundGuest
{
    /**
     * @param  Reservation  $reservation
     * @return void
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function __invoke(Reservation $reservation): void
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $refund = $stripe->refunds->create([
            'payment_intent' => $reservation->payment_intent,
            'metadata' => [
                'reservation' => $reservation->ulid,
            ],
        ]);

        /** @var User|null $authUser */
        $authUser = Auth::user();

        $amount = moneyFormatter($refund->amount);

        activity()
            ->causedBy($authUser)
            ->performedOn($reservation)
            ->withProperties([
                'user' => $authUser?->email,
                'reservation' => $reservation->ulid,
                'refund' => $refund->id,
                'amount' => $refund->amount,
            ])
            ->log("A refund of $amount has been created.");
    }
}
