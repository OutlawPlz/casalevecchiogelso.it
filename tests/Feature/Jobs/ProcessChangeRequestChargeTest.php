<?php

use App\Jobs\ProcessChangeRequestCharge;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('charge job is dispatched with correct parameters', function () {
    Queue::fake();

    $user = User::factory()->create();
    $changeRequest = ChangeRequest::factory()->create();
    $amount = 10000;

    ProcessChangeRequestCharge::dispatch($user, $amount, $changeRequest);

    Queue::assertPushed(ProcessChangeRequestCharge::class, function ($job) use ($user, $amount, $changeRequest) {
        return $job->user->is($user)
            && $job->amount === $amount
            && $job->changeRequest->is($changeRequest);
    });
});
