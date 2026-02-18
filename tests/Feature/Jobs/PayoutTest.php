<?php

use App\Jobs\Payout;
use App\Models\Payment;
use App\Models\Reservation;
use Spatie\Activitylog\Models\Activity;
use Stripe\Payout as StripePayout;

it('creates a payout', function () {
    $reservation = Reservation::factory()->create();

    Payment::factory()->create([
        'reservation_ulid' => $reservation->ulid,
        'status' => 'succeeded',
        'amount' => 100,
        'fee' => 0,
        'amount_refunded' => 0,
    ]);

    $payout = new Payout($reservation)
        ->withFakeQueueInteractions()
        ->handle();

    expect($payout)
        ->toBeInstanceOf(StripePayout::class)
        ->and($payout->amount)->toBe(100)
        ->and($payout->metadata['reservation'])->toBe($reservation->ulid);

    /** @var Activity $activity */
    $activity = Activity::query()->first();

    expect($activity->description)->toContain('has been requested')
        ->and($activity->properties['payout'])->toStartWith('po_')
        ->and($activity->properties['amount'])->toBe(100);
});
