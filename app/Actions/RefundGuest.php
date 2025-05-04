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
    public function __invoke(Reservation $reservation, int $cents, string $paymentIntent): void
    {
        $stripe = App::make(StripeClient::class);
        /** @var ?User $authUser */
        $authUser = Auth::user();

        $refund = $stripe->refunds->create([
            'payment_intent' => $paymentIntent,
            'amount' => $cents,
            'metadata' => [
                'reservation' => $reservation->ulid,
            ],
        ]);

        $paymentIntents = $reservation->payment_intents;

        array_walk($paymentIntents, function (&$paymentIntent) use ($refund) {
            if ($paymentIntent['id'] === $refund->payment_intent) {
                $paymentIntent['refunds'] = [
                    'id' => $refund->id,
                    'amount' => $refund->amount,
                    'status' => $refund->status,
                ];
            }
        });

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
    }
}
