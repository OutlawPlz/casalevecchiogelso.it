<?php

use App\Jobs\Refund;
use App\Models\Reservation;
use Illuminate\Support\Facades\Queue;

test('refund job is dispatched with correct parameters', function () {
    Queue::fake();

    $reservation = Reservation::factory()->create();
    $amount = 5000;
    $metadata = ['change_request' => 'ulid_123'];

    Refund::dispatch($reservation, $amount, $metadata);

    Queue::assertPushed(Refund::class, function ($job) use ($reservation, $amount, $metadata) {
        return $job->reservation->is($reservation)
            && $job->amount === $amount
            && $job->metadata === $metadata;
    });
});
