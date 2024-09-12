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
     */
    public function __invoke(Reservation $reservation, int $amount = 0): void
    {
        $this->stripe = App::make(StripeClient::class);

        if (! $amount) $amount = $this->calculateRefundAmount($reservation);

        $refund = $this->stripe->refunds->create([
            'payment_intent' => $reservation->payment_intent,
            'amount' => $amount,
            'metadata' => [
                'reservation' => $reservation->ulid,
            ],
        ]);

        /** @var User|null $authUser */
        $authUser = Auth::user();

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

    /**
     * @param Reservation $reservation
     * @return int
     * @throws ApiErrorException
     */
    protected function calculateRefundAmount(Reservation $reservation): int
    {
        $paymentIntent = $this->stripe->paymentIntents->retrieve($reservation->payment_intent);

        if (now()->isAfter($reservation->check_in)) {
            throw ValidationException::withMessages([
                'refund' => __('This reservation is not eligible for a refund.'),
            ]);
        }

        if (now()->isBetween(...$reservation->refundPeriod)) {
            return $paymentIntent->amount * $reservation->cancellation_policy->refundFactor();
        }

        return $paymentIntent->amount;
    }
}
