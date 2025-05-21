<?php

use App\Actions\ApproveChangeRequest;
use App\Enums\ChangeRequestStatus as Status;
use App\Models\ChangeRequest;

test('change request can be approved', function () {
    $changeRequest = ChangeRequest::factory()->create();

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
