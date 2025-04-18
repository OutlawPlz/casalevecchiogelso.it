<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;
use function App\Helpers\dates_in_range;

class Calendar
{
    /** @var list<array{uid:string,start_at:string,end_at:string,unavailable_dates:string[],summary:string}> */
    protected array $events;

    /** @var string[] */
    protected array $defaultServices = ['database', 'airbnb'];

    public function __construct()
    {
        $this->events = Storage::json('calendar.json') ?? [];
    }

    public function isAvailable(DateTimeImmutable $checkIn, DateTimeImmutable $checkOut, ?Reservation $ignore = null): bool
    {
        $reservedPeriod = new Period($checkIn, $checkOut, Precision::DAY(), Boundaries::EXCLUDE_END());

        $events = array_filter($this->events, fn ($event) => ! $ignore || ! str_starts_with($event['uid'], $ignore->ulid));

        foreach ($events as $event) {
            $eventPeriod = Period::make(
                $event['start_at'],
                $event['end_at'],
                Precision::DAY(),
                Boundaries::EXCLUDE_END(),
                'Y-m-d\TH:i:s.u\Z'
            );

            $overlaps = $eventPeriod->overlapsWith($reservedPeriod);

            if ($overlaps) return false;
        }

        return true;
    }

    public function isNotAvailable(DateTimeImmutable $checkIn, DateTimeImmutable $checkOut): bool
    {
        return ! $this->isAvailable($checkIn, $checkOut);
    }

    public function sync(): void
    {
        $this->syncFromServices();
    }

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

    public function fromDatabase(): array
    {
        /** @var Collection<int, Reservation> $reservations */
        $reservations = Reservation::query()
            ->where([
                ['check_in', '>', today()],
                ['status', '=', ReservationStatus::CONFIRMED]
            ])
            ->get();

        $events = [];

        foreach ($reservations as $reservation) {
            $events[] = [
                'uid' => "$reservation->ulid@database",
                'start_at' => $reservation->check_in->toISOString(),
                'end_at' => $reservation->check_out->toISOString(),
                'unavailable_dates' => dates_in_range($reservation->check_in, $reservation->check_out),
                'summary' => $reservation->summary
            ];

            $wholePreparationTime = [
                'check_in_prep' => $reservation->checkInPreparationTime,
                'check_out_prep' => $reservation->checkOutPreparationTime,
            ];

            /** @var CarbonImmutable[] $preparationTime */
            foreach ($wholePreparationTime as $prePost => $preparationTime) {
                if (! $preparationTime) continue;

                [$startAt, $endAt] = $preparationTime;

                $events[] = [
                    'uid' => "$reservation->ulid-$prePost@database",
                    'start_at' => $startAt->toISOString(),
                    'end_at' => $endAt->toISOString(),
                    'unavailable_dates' => dates_in_range($startAt, $endAt),
                    'summary' => 'Preparation time'
                ];
            }
        }

        return $events;
    }

    public function fromAirbnb(?string $ics = null): array
    {
        $ics ??= config('services.airbnb.ics_link');

        // I have to use withUserAgent(), otherwise AirBnB will return 429 code.
        $response = Http::withUserAgent('')->get($ics);

        $body = str_replace("\n ", '', $response->body());

        preg_match_all('/BEGIN:VEVENT(?s).*?END:VEVENT/', $body, $matches);

        $reservations = [];

        foreach ($matches[0] as $match) {
            $event = [];

            $lines = explode("\r\n", $match);

            foreach ($lines as $line) {
                [$key, $value] = explode(':', $line, 2);

                $event[$key] = $value;
            }

            $startAt = new CarbonImmutable($event['DTSTART;VALUE=DATE']);
            $endAt = new CarbonImmutable($event['DTEND;VALUE=DATE']);

            $reservations[] = [
                'uid' => $event['UID'],
                'start_at' => $startAt->toISOString(),
                'end_at' => $endAt->toISOString(),
                'unavailable_dates' => dates_in_range($startAt, $endAt),
                'summary' => $event['DESCRIPTION'] ?? $event['SUMMARY']
            ];
        }

        return $reservations;
    }

    /**
     * @return string[]
     */
    public function unavailableDates(): array
    {
        return call_user_func_array(
            'array_merge',
            array_column($this->events, 'unavailable_dates')
        );
    }
}
