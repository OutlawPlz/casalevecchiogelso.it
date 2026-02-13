<?php

use App\Jobs\Charge;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('charge job is dispatched with correct parameters', function () {
    Queue::fake();

    $user = User::factory()->create();
    $amount = 10000;
    $metadata = ['reservation' => 'ulid_123', 'change_request' => 'ulid_456'];

    Charge::dispatch($user, $amount, $metadata);

    Queue::assertPushed(Charge::class, function ($job) use ($user, $amount, $metadata) {
        return $job->user->is($user)
            && $job->amount === $amount
            && $job->metadata === $metadata;
    });
});
