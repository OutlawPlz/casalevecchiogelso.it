<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Calendar
{
    protected array $reservedDates;

    /** @var string[] */
    protected array $defaultServices = ['database', 'airbnb'];

    public function __construct()
    {
        $this->reservedDates = Storage::json('calendar.json') ?? [];
    }

    /**
     * @param \DateTimeInterface $checkin
     * @param \DateTimeInterface $checkout
     * @return bool
     */
    public function isAvailable(\DateTimeInterface $checkin, \DateTimeInterface $checkout): bool
    {
        // TODO: Whether the dates are available or not.
    }

    /**
     * @param string ...$services
     * @return void
     */
    public function syncFromServices(string ...$services): void
    {
        if (! $services) $services = $this->defaultServices;

        $reservedDates = [];

        foreach ($services as $service) {
            $fromService = 'from' . Str::studly($service);

            $reservedDates += $this->$fromService();
        }

        // TODO: I'd like to order the dates inside the array.

        $this->reservedDates = $reservedDates;
    }

    /**
     * @return array
     */
    public function fromDatabase(): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<Reservation> $reservations */
        $reservations = Reservation::query()->where('check_in', '>', today())->get();

        $reservedDates = [];

        foreach ($reservations as $reservation) {
            if ($reservation->preparation_time) $reservedDates[] = $reservation->check_in_preparation_time;

            $reservedDates[] = [$reservation->check_in, $reservation->check_out];

            if ($reservation->preparation_time) $reservedDates[] = $reservation->check_out_preparation_time;
        }

        return $reservedDates;
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
                'start_at' => (new \DateTime($event['DTSTART;VALUE=DATE']))->format('Y-m-d'),
                'end_at' => (new \DateTime($event['DTEND;VALUE=DATE']))->format('Y-m-d'),
                'summary' => $event['DESCRIPTION'] ?? $event['SUMMARY']
            ];
        }

        return $reservations;
    }
}
