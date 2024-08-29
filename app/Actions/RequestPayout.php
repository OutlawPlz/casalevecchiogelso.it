<?php

namespace App\Actions;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class RequestPayout
{
    /**
     * @param  Reservation  $reservation
     * @return void
     * @throws ApiErrorException
     */
    public function __invoke(Reservation $reservation): void
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $paymentIntent = $stripe->paymentIntents->retrieve(
            $reservation->payment_intent,
            ['expand' => ['latest_charge.balance_transaction']
        ]);

        $balanceTransaction = $paymentIntent->latest_charge->balance_transaction;

        $amount = (new \NumberFormatter('it', \NumberFormatter::CURRENCY))
            ->formatCurrency($balanceTransaction->net / 100, $balanceTransaction->currency);

        if ($balanceTransaction->net < config('stripe.min_payout_amount')) {
            activity()
                ->performedOn($reservation)
                ->withProperties([
                    'reservation' => $reservation->ulid,
                    'user' => $authUser?->email,
                    'amount' => $balanceTransaction->net,
                    'min_amount' => config('stripe.min_payout_amount'),
                ])
                ->log("A payout of $amount is under the minimum amount.");

            return;
        }

        $payout = $stripe->payouts->create([
            'amount' => $balanceTransaction->net,
            'currency' => $balanceTransaction->currency,
            'metadata' => [
                'reservation' => $reservation->ulid,
            ],
        ]);

        $reservation->update(['payout' => $payout->id]);

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $authUser?->email,
                'payout' => $payout->id,
                'amount' => $balanceTransaction->net,
            ])
            ->log("A payout of $amount has been requested.");
    }
}
