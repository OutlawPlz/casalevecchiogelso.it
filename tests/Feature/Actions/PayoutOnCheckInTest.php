<?php

use App\Actions\PayoutOnCheckIn;
use App\Jobs\Payout;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;

it('payouts on check-in date', function () {
    Queue::fake();

    $reservation = Reservation::factory()->create(['check_in' => today()]);

    Payment::factory()->create([
        'reservation_ulid' => $reservation->ulid,
        'status' => 'succeeded',
        'amount' => 10000,
        'fee' => 0,
        'amount_refunded' => 0,
    ]);

    Reservation::factory()->create(['check_in' => today()->addWeek()]);

    (new PayoutOnCheckIn)();

    Queue::assertPushedTimes(Payout::class);
});

it('skips payout if the amount is under the minimum', function () {
    Queue::fake();

    $reservation = Reservation::factory()->create(['check_in' => today()]);

    Payment::factory()->create([
        'reservation_ulid' => $reservation->ulid,
        'status' => 'succeeded',
        'amount' => 10,
        'fee' => 0,
        'amount_refunded' => 0,
    ]);

    (new PayoutOnCheckIn)();

    Queue::assertNothingPushed();

    /** @var Activity $activity */
    $activity = Activity::query()->first();

    expect($activity->description)
        ->toContain('under the minimum amount')
        ->and($activity->properties['amount'])->toBe(10);
});
