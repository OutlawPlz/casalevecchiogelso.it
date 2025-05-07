<?php

namespace App\Actions;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use function App\Helpers\money_formatter;

class RefundGuest
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(Reservation $reservation, int $cents): void
    {
        if ($reservation->amountPaid() < $cents) {
            throw new \RuntimeException('The refund amount is greater than the total amount paid.');
        }

        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);
        /** @var ?User $authUser */
        $authUser = Auth::user();

        $paymentIntents = $reservation->payment_intents;

        foreach ($paymentIntents as $paymentIntent) {
            $refundAmount = min($cents, $paymentIntent['amount']);

            $refund = $stripe->refunds->create([
                'payment_intent' => $paymentIntent['id'],
                'amount' => $refundAmount,
                'metadata' => [
                    'reservation' => $reservation->ulid,
                ],
            ]);

            $formattedAmount = money_formatter($refund->amount);

            activity()
                ->causedBy($authUser)
                ->performedOn($reservation)
                ->withProperties([
                    'user' => $authUser?->email,
                    'reservation' => $reservation->ulid,
                    'refund' => $refund->id,
                    'amount' => $refund->amount,
                ])
                ->log("A refund of $formattedAmount has been created.");

            $cents -= $refundAmount;

            if (! $cents) break;
        }
    }
}
