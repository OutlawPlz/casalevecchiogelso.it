<?php

use App\Actions\ApproveChangeRequest;
use App\Enums\ChangeRequestStatus as Status;
use App\Jobs\Charge;
use App\Jobs\Refund;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;

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

test('change request can be cancelled', function () {
    $guest = User::factory()->create();
    $host = User::factory()->host()->create();
    $changeRequest = ChangeRequest::factory()
        ->for($guest, 'user')
        ->create();

    $this->actingAs($host)
        ->withSession(['_token' => 'test'])
        ->post("/reservations/{$changeRequest->reservation->ulid}/change-requests/{$changeRequest->id}/cancel", [
            '_token' => 'test',
        ]);

    expect($changeRequest->refresh()->status)
        ->toBe(Status::CANCELLED)
        ->and(Activity::query()->where('subject_id', $changeRequest->reservation->id)->latest()->first())
        ->not->toBeNull();
});

test('change request can be rejected', function () {
    $host = User::factory()->host()->create();
    $changeRequest = ChangeRequest::factory()->create();

    $this->actingAs($host)
        ->withSession(['_token' => 'test'])
        ->post("/reservations/{$changeRequest->reservation->ulid}/change-requests/{$changeRequest->id}/reject", [
            '_token' => 'test',
        ]);

    expect($changeRequest->refresh()->status)
        ->toBe(Status::REJECTED)
        ->and(Activity::query()->where('subject_id', $changeRequest->reservation->id)->latest()->first())
        ->not->toBeNull();
});

test('approval dispatches charge job when price difference is positive', function () {
    Queue::fake();

    $host = User::factory()->host()->create();

    $changeRequest = ChangeRequest::factory()->amountIncrease()->create();

    $this->actingAs($host)
        ->post(route('change_request.approve', [$changeRequest->reservation, $changeRequest]));

    Queue::assertPushed(Charge::class);
});

test('approval dispatches refund job when price difference is negative', function () {
    Queue::fake();

    $host = User::factory()->host()->create();

    $changeRequest = ChangeRequest::factory()->create();

    $this
        ->actingAs($host)
        ->post(route('change_request.approve', [$changeRequest->reservation, $changeRequest]));

    Queue::assertPushed(Refund::class);
});
