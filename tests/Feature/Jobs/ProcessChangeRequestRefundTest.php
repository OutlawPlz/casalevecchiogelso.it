<?php

use App\Jobs\ProcessChangeRequestRefund;
use App\Models\ChangeRequest;
use Illuminate\Support\Facades\Queue;

test('refund job is dispatched with correct parameters', function () {
    Queue::fake();

    $changeRequest = ChangeRequest::factory()->create();
    $amount = -5000;

    ProcessChangeRequestRefund::dispatch($changeRequest, $amount);

    Queue::assertPushed(ProcessChangeRequestRefund::class, function ($job) use ($changeRequest, $amount) {
        return $job->changeRequest->is($changeRequest)
            && $job->amount === $amount;
    });
});
