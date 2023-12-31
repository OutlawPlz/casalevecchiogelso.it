<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class Calendar
{
    public array $events;

    /** @var string[] */
    protected array $defaultServices = ['database', 'airbnb'];

    public function __construct()
    {
        $this->events = Storage::json('calendar.json') ?? [];
    }

    /**
     * @param \DateTimeImmutable $checkIn
     * @param \DateTimeImmutable $checkOut
     * @return bool
     */
    public function isAvailable(\DateTimeImmutable $checkIn, \DateTimeImmutable $checkOut): bool
    {
        $reservedPeriod = new Period($checkIn, $checkOut, Precision::DAY(), Boundaries::EXCLUDE_END());

        foreach ($this->events as $event) {
            $eventPeriod = new Period(
                new \DateTimeImmutable($event['start_at']),
                new \DateTimeImmutable($event['end_at']),
                Precision::DAY(),
                Boundaries::EXCLUDE_END()
            );

            $overlaps = $eventPeriod->overlapsWith($reservedPeriod);

            if ($overlaps) return false;
        }

        return true;
    }

    /**
     * @param \DateTimeImmutable $checkIn
     * @param \DateTimeImmutable $checkOut
     * @return bool
     */
    public function isNotAvailable(\DateTimeImmutable $checkIn, \DateTimeImmutable $checkOut): bool
    {
        return ! $this->isAvailable($checkIn, $checkOut);
    }

    /**
     * @param string ...$services
     * @return void
     */
    public function syncFromServices(string ...$services): void
    {
        if (! $services) $services = $this->defaultServices;

        $events = [];

        foreach ($services as $service) {
            $fromService = 'from' . Str::studly($service);

            $events = array_merge($events, $this->$fromService());
        }

        $this->events = $events;

        Storage::put('calendar.json', json_encode($this->events, JSON_PRETTY_PRINT));
    }

    /**
     * @return array
     */
    public function fromDatabase(): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<Reservation> $reservations */
        $reservations = Reservation::query()->where('check_in', '>', today())->get();

        $events = [];

        foreach ($reservations as $reservation) {
            $events[] = [
                'uid' => $reservation->uid,
                'start_at' => $reservation->check_in,
                'end_at' => $reservation->check_out,
                'summary' => $reservation->summary
            ];

            if (! $reservation->preparation_time) continue;

            foreach (['check_in_preparation_time', 'check_out_preparation_time'] as $preparationTime) {
                list($startAt, $endAt) = $reservation->$preparationTime;

                $events[] = [
                    'uid' => $reservation->uid,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'summary' => 'Preparation time'
                ];
            }
        }

        return $events;
    }

    /**
     * @param string|null $ics
     * @return array
     * @throws \Exception
     */
    public function fromAirbnb(?string $ics = null): array
    {
        $ics ??= env('AIRBNB_ICS_LINK');

        $response = Http::get($ics);

        $body = str_replace("\n ", '', $response->body());

        preg_match_all('/BEGIN:VEVENT(?s).*?END:VEVENT/', $body, $matches);

        $reservations = [];

        foreach ($matches[0] as $match) {
            $event = [];

            $lines = explode("\n", $match);

            foreach ($lines as $line) {
                [$key, $value] = explode(':', $line, 2);

                $event[$key] = $value;
            }

            $reservations[] = [
                'uid' => $event['UID'],
                'start_at' => (new \DateTimeImmutable($event['DTSTART;VALUE=DATE'])),
                'end_at' => (new \DateTimeImmutable($event['DTEND;VALUE=DATE'])),
                'summary' => $event['DESCRIPTION'] ?? $event['SUMMARY']
            ];
        }

        return $reservations;
    }
}
