<?php

use App\Services\Calendar;
use Illuminate\Support\Facades\Storage;

$events = <<<JSON
[
    {
        "uid": "01HHJM60BCNF4V6CB13QWEXKPC",
        "start_at": "2023-12-20T00:00:00.000000Z",
        "end_at": "2023-12-27T00:00:00.000000Z",
        "summary": null
    },
    {
        "uid": "01HHJM60BCNF4V6CB13QWEXKPC",
        "start_at": "2023-12-19T00:00:00.000000Z",
        "end_at": "2023-12-20T00:00:00.000000Z",
        "summary": "Preparation time"
    },
    {
        "uid": "01HHJM60BCNF4V6CB13QWEXKPC",
        "start_at": "2023-12-27T00:00:00.000000Z",
        "end_at": "2023-12-28T00:00:00.000000Z",
        "summary": "Preparation time"
    }
]
JSON;

beforeEach(fn() => Storage::fake()->put('calendar.json', $events));

it('prevents overlapping events', function () {
    $calendar = new Calendar();

    $overlappingPeriod = [
        new DateTimeImmutable('2023-12-24'),
        new DateTimeImmutable('2024-1-2')
    ];

    $isNotAvailable = $calendar->isAvailable(...$overlappingPeriod);

    expect($isNotAvailable)->toBeFalse();
});

it('prevents nested events', function () {
    $calendar = new Calendar();

    $containedPeriod = [
        new DateTimeImmutable('2023-12-24'),
        new DateTimeImmutable('2023-12-26')
    ];

    $isNotAvailable = $calendar->isAvailable(...$containedPeriod);

    expect($isNotAvailable)->toBeFalse();
});

it('allows separate events', function () {
    $calendar = new Calendar();

    $availablePeriod = [
        new DateTimeImmutable('2023-12-28'),
        new DateTimeImmutable('2024-1-2')
    ];

    $isAvailable = $calendar->isAvailable(...$availablePeriod);

    expect($isAvailable)->toBeTrue();
});
