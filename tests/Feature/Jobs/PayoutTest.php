<?php

use App\Jobs\Payout;
use App\Models\Payment;
use App\Models\Reservation;
use Spatie\Activitylog\Models\Activity;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Payout as StripePayout;
use Stripe\StripeClient;

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

function fakePayouts(Throwable $exception): void
{
    $service = Mockery::mock();

    $service->shouldReceive('create')->andThrow($exception);

    $stripe = new stdClass;

    $stripe->payouts = $service;

    app()->instance(StripeClient::class, $stripe);
}

it('fails permanently', function () {
    $reservation = Reservation::factory()->create();

    Payment::factory()->create([
        'reservation_ulid' => $reservation->ulid,
        'status' => 'succeeded',
        'amount' => 100,
        'fee' => 0,
        'amount_refunded' => 0,
    ]);

    fakePayouts(new InvalidRequestException('Insufficient balance'));

    $payout = new Payout($reservation)->withFakeQueueInteractions();

    expect(fn () => $payout->handle())->toThrow(InvalidRequestException::class);

    $payout->assertFailedWith(InvalidRequestException::class);
});

it('retries failed jobs', function () {
    $reservation = Reservation::factory()->create();

    Payment::factory()->create([
        'reservation_ulid' => $reservation->ulid,
        'status' => 'succeeded',
        'amount' => 100,
        'fee' => 0,
        'amount_refunded' => 0,
    ]);

    fakePayouts(new ApiConnectionException('Timeout'));

    $payout = new Payout($reservation)->withFakeQueueInteractions();

    expect(fn () => $payout->handle())->toThrow(ApiConnectionException::class);

    $payout->assertNotFailed();
});
