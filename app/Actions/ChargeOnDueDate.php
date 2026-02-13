<?php

namespace App\Actions;

use App\Jobs\Charge;
use App\Models\Reservation;
use Illuminate\Support\Collection;

class ChargeOnDueDate
{
    public function __invoke(): void
    {
        /** @var Collection<Reservation> $reservations */
        $reservations = Reservation::query()->where('due_date', today())->get();

        foreach ($reservations as $reservation) {
            Charge::dispatch($reservation->user, $reservation->tot, [
                'reservation' => $reservation->ulid,
                'retry_on_failure' => true,
            ]);
        }
    }
}
