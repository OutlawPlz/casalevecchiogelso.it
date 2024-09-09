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
     * @param Reservation $reservation
     * @param int $amount
     * @return void
     * @throws ApiErrorException
     */
    public function __invoke(Reservation $reservation, int $amount = 0): void
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        if (! $amount) $amount = $this->calculateRefundAmount($reservation);

        $refund = $stripe->refunds->create([
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
     */
    protected function calculateRefundAmount(Reservation $reservation): int
    {
        return 0;
    }
}
