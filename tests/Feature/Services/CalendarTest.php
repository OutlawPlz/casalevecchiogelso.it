<?php

use App\Models\Reservation;
use App\Services\Calendar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('returns true when the period does not overlap any event', function () {
    Storage::fake();

    Storage::put('calendar.json', json_encode([
        [
            'uid' => 'event1@database',
            'start_at' => '2025-06-01T00:00:00.000000Z',
            'end_at' => '2025-06-07T00:00:00.000000Z',
            'unavailable_dates' => ['2025-06-02', '2025-06-03', '2025-06-04', '2025-06-05', '2025-06-06', '2025-06-07'],
            'summary' => 'Test',
        ],
    ]));

    $calendar = new Calendar;

    expect($calendar->isAvailable(
        new DateTimeImmutable('2025-06-10'),
        new DateTimeImmutable('2025-06-15'),
    ))->toBeTrue();
});

it('returns false when the period overlaps an event', function () {
    Storage::fake();

    Storage::put('calendar.json', json_encode([
        [
            'uid' => 'event1@database',
            'start_at' => '2025-06-01T00:00:00.000000Z',
            'end_at' => '2025-06-07T00:00:00.000000Z',
            'unavailable_dates' => ['2025-06-02', '2025-06-03', '2025-06-04', '2025-06-05', '2025-06-06', '2025-06-07'],
            'summary' => 'Test',
        ],
    ]));

    $calendar = new Calendar;

    expect($calendar->isAvailable(
        new DateTimeImmutable('2025-06-05'),
        new DateTimeImmutable('2025-06-12'),
    ))->toBeFalse();
});

it('ignores events belonging to the given reservation', function () {
    $reservation = Reservation::factory()->make();

    Storage::fake();

    Storage::put('calendar.json', json_encode([
        [
            'uid' => "{$reservation->ulid}@database",
            'start_at' => '2025-06-01T00:00:00.000000Z',
            'end_at' => '2025-06-07T00:00:00.000000Z',
            'unavailable_dates' => [],
            'summary' => 'Test',
        ],
    ]));

    $calendar = new Calendar;

    expect($calendar->isAvailable(
        new DateTimeImmutable('2025-06-05'),
        new DateTimeImmutable('2025-06-12'),
        $reservation,
    ))->toBeTrue();
});

test('isNotAvailable returns the inverse of isAvailable', function () {
    Storage::fake();

    $calendar = new Calendar;

    expect($calendar->isAvailable(
        new DateTimeImmutable('2025-06-05'),
        new DateTimeImmutable('2025-06-12')
    ))
        ->toBeTrue()
        ->and($calendar->isNotAvailable(
            new DateTimeImmutable('2025-06-05'),
            new DateTimeImmutable('2025-06-12'))
        )->toBeFalse();
});

it('returns events for confirmed future reservations', function () {
    $reservation = Reservation::factory()->create();

    $calendar = new Calendar;

    $events = $calendar->fromDatabase();

    $uids = array_column($events, 'uid');

    expect($events)->toHaveCount(3)
        ->and($uids)->toContain("{$reservation->ulid}@database")
        ->and($uids)->toContain("{$reservation->ulid}-check_in_prep@database")
        ->and($uids)->toContain("{$reservation->ulid}-check_out_prep@database");
});

it('excludes reservations with a past check-in from fromDatabase', function () {
    Reservation::factory()->inProgress()->create();

    $calendar = new Calendar;

    expect($calendar->fromDatabase())->toBeEmpty();
});

it('parses an ics feed and returns events', function () {
    $ics = implode("\r\n", [
        'BEGIN:VCALENDAR',
        'BEGIN:VEVENT',
        'UID:airbnb-reservation@airbnb.com',
        'DTSTART;VALUE=DATE:20251001',
        'DTEND;VALUE=DATE:20251007',
        'SUMMARY:Reserved',
        'DESCRIPTION:Some Guest',
        'END:VEVENT',
        'END:VCALENDAR',
    ]);

    Http::fake(['*' => Http::response($ics)]);

    $calendar = new Calendar;

    $events = $calendar->fromAirbnb('https://example.com/calendar.ics');

    expect($events)->toHaveCount(1)
        ->and($events[0]['uid'])->toBe('airbnb-reservation@airbnb.com')
        ->and($events[0]['summary'])->toBe('Some Guest')
        ->and($events[0]['unavailable_dates'])->toBe([
            '2025-10-02', '2025-10-03', '2025-10-04', '2025-10-05', '2025-10-06', '2025-10-07',
        ]);
});

it('syncs events from specified services and stores them', function () {
    Storage::fake();

    $reservation = Reservation::factory()->create();

    (new Calendar)->syncFromServices('database');

    $stored = Storage::json('calendar.json');
    $uids = array_column($stored, 'uid');

    expect($stored)->toHaveCount(3)
        ->and($uids)->toContain("{$reservation->ulid}@database");
});

it('returns all unavailable dates merged from all events', function () {
    Storage::fake();

    Storage::put('calendar.json', json_encode([
        [
            'uid' => 'event1@database',
            'start_at' => '2025-06-01T00:00:00.000000Z',
            'end_at' => '2025-06-03T00:00:00.000000Z',
            'unavailable_dates' => ['2025-06-02', '2025-06-03'],
            'summary' => 'Event 1',
        ],
        [
            'uid' => 'event2@database',
            'start_at' => '2025-06-10T00:00:00.000000Z',
            'end_at' => '2025-06-11T00:00:00.000000Z',
            'unavailable_dates' => ['2025-06-11'],
            'summary' => 'Event 2',
        ],
    ]));

    $calendar = new Calendar;

    expect($calendar->unavailableDates())->toBe(['2025-06-02', '2025-06-03', '2025-06-11']);
});

it('excludes unavailable dates for the given reservation', function () {
    $reservation = Reservation::factory()->make();

    Storage::fake();

    Storage::put('calendar.json', json_encode([
        [
            'uid' => "{$reservation->ulid}@database",
            'start_at' => '2025-06-01T00:00:00.000000Z',
            'end_at' => '2025-06-05T00:00:00.000000Z',
            'unavailable_dates' => ['2025-06-02', '2025-06-03', '2025-06-04', '2025-06-05'],
            'summary' => 'My reservation',
        ],
        [
            'uid' => 'other-event@database',
            'start_at' => '2025-06-10T00:00:00.000000Z',
            'end_at' => '2025-06-11T00:00:00.000000Z',
            'unavailable_dates' => ['2025-06-11'],
            'summary' => 'Other event',
        ],
    ]));

    $calendar = new Calendar;

    expect($calendar->unavailableDates($reservation))->toBe(['2025-06-11']);
});
