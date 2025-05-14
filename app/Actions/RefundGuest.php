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
        /** @var ?User $authUser */
        $authUser = Auth::user();

        foreach ($reservation->payments as $payment) {
            $amount = min($cents, $payment->amount);

            $refund = $payment->refund($amount);

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

            $cents -= $amount;

            if (! $cents) break;
        }
    }
}
