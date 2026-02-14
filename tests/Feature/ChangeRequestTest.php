<?php

use App\Actions\ApproveChangeRequest;
use App\Enums\ChangeRequestStatus as Status;
use App\Jobs\Charge;
use App\Jobs\Refund;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('change request can be approved', function () {
    $host = User::factory()->host()->create();

    $changeRequest = ChangeRequest::factory()->create();

    $this->actingAs($host);

    (new ApproveChangeRequest)($changeRequest);

    $reservation = $changeRequest->reservation;

    expect($changeRequest->status)
        ->toBe(Status::APPROVED)
        ->and([
            'check_in' => $reservation->check_in,
            'check_out' => $reservation->check_out,
            'guests' => $reservation->guest_count,
            'price_list' => $reservation->price_list,
        ])->toMatchArray([
            'check_in' => $changeRequest->toReservation->check_in,
            'check_out' => $changeRequest->toReservation->check_out,
            'guests' => $changeRequest->toReservation->guest_count,
            'price_list' => $changeRequest->toReservation->price_list,
        ]);

});

it('dispatches a charge when price difference is positive', function () {
    Queue::fake();

    $host = User::factory()->host()->create();

    $changeRequest = ChangeRequest::factory()->amountIncrease()->create();

    $this
        ->actingAs($host)
        ->post(route('change_request.approve', [$changeRequest->reservation, $changeRequest]));

    Queue::assertPushed(Charge::class, function ($job) {
        return $job->idempotencyKey !== null;
    });
});

it('dispatches a refund when price difference is negative', function () {
    Queue::fake();

    $host = User::factory()->host()->create();

    $changeRequest = ChangeRequest::factory()->amountDecrease()->create();

    $this
        ->actingAs($host)
        ->post(route('change_request.approve', [$changeRequest->reservation, $changeRequest]));

    Queue::assertPushed(Refund::class, function ($job) {
        return $job->idempotencyKey !== null;
    });
});
