<?php

namespace App\Actions;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use function App\Helpers\money_formatter;

class RefundGuest
{
    protected StripeClient $stripe;

    /**
     * @param Reservation $reservation
     * @param int $amount
     * @return void
     * @throws ApiErrorException
     * @throws ValidationException
     */
    public function __invoke(Reservation $reservation, int $amount = 0): void
    {
        $this->stripe = App::make(StripeClient::class);

        /** @var User|null $authUser */
        $authUser = Auth::user();

        $paymentIntent = $this->stripe->paymentIntents->retrieve($reservation->payment_intent);

        $amount |= $paymentIntent->amount * $reservation->refundFactor();

        if (! $amount) {
            $message = 'The reservation is not eligible for a refund.';

            activity()
                ->causedBy($authUser)
                ->performedOn($reservation)
                ->withProperties([
                    'user' => $authUser?->email,
                    'reservation' => $reservation->ulid,
                ])
                ->log($message);

            throw ValidationException::withMessages([
                'refund_denied' => __($message),
            ]);
        }

        // TODO: Catch ApiError and show message to the user.

        $refund = $this->stripe->refunds->create([
            'payment_intent' => $reservation->payment_intent,
            'amount' => $amount,
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
    }
}
