<?php

namespace App\Actions;

use App\Jobs\Payout;
use App\Models\Reservation;
use Illuminate\Support\Collection;

use function App\Helpers\money_format;

class PayoutOnCheckIn
{
    public function __invoke(): void
    {
        /** @var Collection<Reservation> $reservations */
        $reservations = Reservation::query()->with('payments')->where('check_in', today())->get();

        foreach ($reservations as $reservation) {
            $netAmount = $reservation->payments->sum(fn ($payment) => $payment->netAmount);

            if ($netAmount < config('services.stripe.min_payout_amount')) {
                activity()
                    ->performedOn($reservation)
                    ->withProperties([
                        'reservation' => $reservation->ulid,
                        'amount' => $netAmount,
                        'min_amount' => config('services.stripe.min_payout_amount'),
                    ])
                    ->log('A payout of '.money_format($netAmount).' is under the minimum amount.');

                continue;
            }

            Payout::dispatch($reservation);
        }
    }
}
