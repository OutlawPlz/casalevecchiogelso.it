<?php

use App\Actions\ApproveChangeRequest;
use App\Enums\ChangeRequestStatus as Status;
use App\Jobs\ProcessChangeRequestCharge;
use App\Jobs\ProcessChangeRequestRefund;
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
    $changeRequest = ChangeRequest::factory()->create([
        'to' => [
            'check_in' => today()->addWeek(),
            'check_out' => today()->addWeeks(3),
            'guest_count' => 10,
            'price_list' => [
                [
                    'product' => 'prod_QFGF5ANGoEMpOI',
                    'name' => 'Overnight stay',
                    'description' => 'Test',
                    'price' => 'price_1POlisAKSJP4UmE2U0xe8DXq',
                    'unit_amount' => 25000,
                    'quantity' => 14,
                ],
            ],
        ],
    ]);

    $this->actingAs($host)
        ->withSession(['_token' => 'test'])
        ->post("/reservations/{$changeRequest->reservation->ulid}/change-requests/{$changeRequest->id}/approve", [
            '_token' => 'test',
        ]);

    Queue::assertPushed(ProcessChangeRequestCharge::class);
});

test('approval dispatches refund job when price difference is negative', function () {
    Queue::fake();

    $host = User::factory()->host()->create();
    $changeRequest = ChangeRequest::factory()->create([
        'to' => [
            'check_in' => today()->addWeek(),
            'check_out' => today()->addWeeks(2)->subDays(2),
            'guest_count' => 7,
            'price_list' => [
                [
                    'product' => 'prod_QFGF5ANGoEMpOI',
                    'name' => 'Overnight stay',
                    'description' => 'Test',
                    'price' => 'price_1POlisAKSJP4UmE2U0xe8DXq',
                    'unit_amount' => 25000,
                    'quantity' => 3,
                ],
            ],
        ],
    ]);

    $this->actingAs($host)
        ->withSession(['_token' => 'test'])
        ->post("/reservations/{$changeRequest->reservation->ulid}/change-requests/{$changeRequest->id}/approve", [
            '_token' => 'test',
        ]);

    Queue::assertPushed(ProcessChangeRequestRefund::class);
});
