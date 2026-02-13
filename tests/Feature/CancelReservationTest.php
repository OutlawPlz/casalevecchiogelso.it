<?php

use App\Actions\CancelReservation;
use App\Enums\ReservationStatus;
use App\Jobs\Refund;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

use function App\Helpers\refund_factor;

/* ----------------------------------------------------------------
 | refund_factor()
 | ---------------------------------------------------------------- */

test('refund_factor returns 1 for host causer', function () {
    $host = User::factory()->host()->create();
    $reservation = Reservation::factory()->create([
        'check_in' => today()->subDay(),
        'check_out' => today()->addDays(3),
    ]);

    expect(refund_factor($reservation, causer: $host))->toBe(1.0);
});

test('refund_factor returns 0 when reservation is in progress', function () {
    $reservation = Reservation::factory()->create([
        'check_in' => today()->subDay(),
        'check_out' => today()->addDays(3),
    ]);

    expect(refund_factor($reservation))->toBe(0.0);
});

test('refund_factor returns cancellation policy factor within refund window', function () {
    $reservation = Reservation::factory()->create([
        'cancellation_policy' => 'moderate',
        'check_in' => today()->addDays(3),
        'check_out' => today()->addDays(10),
    ]);

    expect(refund_factor($reservation))->toBe(0.7);
});

test('refund_factor returns 1 outside refund window', function () {
    $reservation = Reservation::factory()->create([
        'cancellation_policy' => 'moderate',
        'check_in' => today()->addMonth(),
        'check_out' => today()->addMonth()->addWeek(),
    ]);

    expect(refund_factor($reservation))->toBe(1.0);
});

test('in-progress takes precedence over refund window', function () {
    $reservation = Reservation::factory()->create([
        'cancellation_policy' => 'strict',
        'check_in' => today()->subDay(),
        'check_out' => today()->addDays(20),
    ]);

    expect(refund_factor($reservation))->toBe(0.0);
});

test('host causer takes precedence over in-progress', function () {
    $host = User::factory()->host()->create();
    $reservation = Reservation::factory()->create([
        'check_in' => today()->subDay(),
        'check_out' => today()->addDays(3),
    ]);

    expect(refund_factor($reservation, causer: $host))->toBe(1.0);
});

/* ----------------------------------------------------------------
 | CancelReservation action
 | ---------------------------------------------------------------- */

test('cancellation dispatches ProcessRefund job', function () {
    Queue::fake();

    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::CONFIRMED,
        'check_in' => today()->addMonth(),
        'check_out' => today()->addMonth()->addWeek(),
    ]);

    Payment::factory()->create([
        'reservation_ulid' => $reservation->ulid,
        'payment_intent' => 'pi_test',
        'status' => 'succeeded',
        'amount' => 175000,
        'amount_captured' => 175000,
        'amount_refunded' => 0,
        'fee' => 0,
        'customer' => 'cus_test',
    ]);

    (new CancelReservation)($reservation, 'Plans changed.');

    Queue::assertPushed(Refund::class, function ($job) use ($reservation) {
        return $job->reservation->is($reservation)
            && $job->amount === 175000
            && $job->metadata === ['reservation' => $reservation->ulid];
    });
});

test('cancellation sets status to cancelled', function () {
    Queue::fake();

    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::CONFIRMED,
    ]);

    (new CancelReservation)($reservation, 'Plans changed.');

    expect($reservation->fresh()->status)->toBe(ReservationStatus::CANCELLED);
});

test('cancellation does not dispatch job when refund amount is zero', function () {
    Queue::fake();

    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::CONFIRMED,
        'check_in' => today()->subDay(),
        'check_out' => today()->addDays(3),
    ]);

    Payment::factory()->create([
        'reservation_ulid' => $reservation->ulid,
        'payment_intent' => 'pi_test',
        'status' => 'succeeded',
        'amount' => 100000,
        'amount_captured' => 100000,
        'amount_refunded' => 0,
        'fee' => 0,
        'customer' => 'cus_test',
    ]);

    (new CancelReservation)($reservation, 'Too late.');

    Queue::assertNotPushed(Refund::class);
});

/* ----------------------------------------------------------------
 | ReservationPolicy@cancel
 | ---------------------------------------------------------------- */

test('cancel policy allows confirmed reservations', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->for($user)->create([
        'status' => ReservationStatus::CONFIRMED,
    ]);

    expect($user->can('cancel', $reservation))->toBeTrue();
});

test('cancel policy allows pending reservations', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->for($user)->create([
        'status' => ReservationStatus::PENDING,
    ]);

    expect($user->can('cancel', $reservation))->toBeTrue();
});

test('cancel policy blocks non-cancellable statuses', function (ReservationStatus $status) {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->for($user)->create([
        'status' => $status,
    ]);

    expect($user->can('cancel', $reservation))->toBeFalse();
})->with([
    'quote' => ReservationStatus::QUOTE,
    'rejected' => ReservationStatus::REJECTED,
    'cancelled' => ReservationStatus::CANCELLED,
    'completed' => ReservationStatus::COMPLETED,
]);

/* ----------------------------------------------------------------
 | Controller
 | ---------------------------------------------------------------- */

test('cancel controller passes causer to action', function () {
    Queue::fake();

    $user = User::factory()->create();
    $reservation = Reservation::factory()->for($user)->create([
        'status' => ReservationStatus::CONFIRMED,
        'check_in' => today()->addMonth(),
        'check_out' => today()->addMonth()->addWeek(),
    ]);

    $this->actingAs($user)
        ->delete(route('reservation.cancel', $reservation), ['reason' => 'Plans changed.'])
        ->assertRedirect(route('reservation.show', $reservation));

    expect($reservation->fresh()->status)->toBe(ReservationStatus::CANCELLED);
});

test('cancel route accepts DELETE method', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->for($user)->create([
        'status' => ReservationStatus::CONFIRMED,
    ]);

    $this->actingAs($user)
        ->post(route('reservation.cancel', $reservation), ['reason' => 'test'])
        ->assertMethodNotAllowed();
});

/* ----------------------------------------------------------------
 | Reservation::nights
 | ---------------------------------------------------------------- */

test('nights attribute returns total days for stays over 30 days', function () {
    $reservation = Reservation::factory()->create([
        'check_in' => today(),
        'check_out' => today()->addDays(45),
    ]);

    expect($reservation->nights)->toBe(45);
});
