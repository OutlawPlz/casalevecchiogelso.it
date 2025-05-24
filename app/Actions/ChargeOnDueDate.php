<?php

namespace App\Actions;

use App\Models\Reservation;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;

class ChargeOnDueDate
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(): void
    {
        /** @var Collection<Reservation> $reservations */
        $reservations = Reservation::query()->where('due_date', today())->get();

        foreach ($reservations as $reservation) {
            (new Charge)($reservation->user, $reservation->tot, ['metadata' => [
                'reservation' => $reservation->ulid,
                'retry_on_failure' => true
            ]]);
        }
    }
}
