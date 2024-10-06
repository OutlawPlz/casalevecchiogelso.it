<?php

namespace App\Actions;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Stripe\StripeClient;
use function App\Helpers\money_formatter;
use function App\Helpers\refund_amount;

class RefundGuest
{
    protected StripeClient $stripe;

    /**
     * @param Reservation $reservation
     * @param int $amount
     * @return Refund
     * @throws ApiErrorException
     * @throws ValidationException
     */
    public function __invoke(Reservation $reservation, int $amount = 0): Refund
    {
        $this->stripe = App::make(StripeClient::class);

        /** @var User|null $authUser */
        $authUser = Auth::user();

        $amount |= refund_amount($reservation);

        if (! $amount) {
            throw ValidationException::withMessages([
                'refund_denied' => __('The reservation is not eligible for a refund.'),
            ]);
        }

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

        return $refund;
    }

    /**
     * @param Reservation $reservation
     * @return int
     */
    protected function calculateAmount(Reservation $reservation): int
    {
        $stripe = App::make(StripeClient::class);

        $paymentIntent = $stripe->paymentIntents->retrieve($reservation->payment_intent);

        $refundFactor = 1;

        if (now()->isAfter($reservation->check_in)) $refundFactor = 0;

        if (now()->isBetween(...$reservation->refundPeriod)) {
            $refundFactor = $reservation->cancellation_policy->refundFactor();
        }

        return $paymentIntent->amount * $refundFactor;
    }
}
