<?php

use App\Actions\ChargeOnDueDate;
use App\Jobs\Charge;
use App\Models\Reservation;
use Illuminate\Support\Facades\Queue;

it('charges reservations on due date', function () {
    Queue::fake();

    Reservation::factory()->create(['due_date' => today()]);

    Reservation::factory()->create(['due_date' => today()->addWeek()]);

    (new ChargeOnDueDate)();

    Queue::assertPushedTimes(Charge::class);
});
