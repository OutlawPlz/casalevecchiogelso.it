<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\Calendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function create(Request $request, Calendar $calendar): View
    {
        return \view('reservation.create', [
            'unavailableDates' => $calendar->unavailableDates(),
            'checkIn' => $request->check_in,
            'checkOut' => $request->check_out,
            'guestCount' => $request->guest_count,
            'pricePerNight' => config('reservation.price_per_night'),
            'cleaningFee' => config('reservation.cleaning_fee'),
        ]);
    }

    public function store(Request $request, Calendar $calendar): RedirectResponse
    {
        $attributes = $request->validate(self::rules());

        $attributes += [
            'ulid' => Str::ulid(),
            'preparation_time' => new \DateInterval(config('reservation.preparation_time')),
            'price_list' => [
                'price_per_night' => config('reservation.price_per_night'),
                'cleaning_fee' => config('reservation.cleaning_fee'),
            ],
        ];

        $reservation = new Reservation($attributes);

        if ($calendar->isNotAvailable(...$reservation->reserved_period)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        $reservation->save();

        return redirect()->route('reservation.show', [$reservation]);
    }

    public function show(Reservation $reservation, Calendar $calendar): View
    {
        return \view('reservation.show', [
            'unavailableDates' => $calendar->unavailableDates(),
            'reservation' => $reservation
        ]);
    }

    public function update(Request $request, Reservation $reservation, Calendar $calendar): RedirectResponse
    {
        $attributes = $request->validate(self::rules($reservation));

        $reservation->fill($attributes);

        if ($calendar->isNotAvailable(...$reservation->reserved_period)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        $reservation->save();

        return redirect()->route('reservations.show', [$reservation]);
    }

    public static function rules(?Reservation $reservation = null): array
    {
        $rules = [
            'check_in' => ['required', 'date', 'after:tomorrow'],
            'check_out' => ['required', 'date', 'after:start_date'],
            'guest_count' => ['required', 'digits_between:1,10']
        ];

        if (! $reservation) $rules += [
            'first_name' => ['required', 'min:3', 'max:255'],
            'last_name' => ['required', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];

        return $rules;
    }
}
