<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\Calendar;
use App\Services\Price;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    /**
     * @param Request $request
     * @param Calendar $calendar
     * @param Price $price
     * @return View
     * @throws \Exception
     */
    public function create(Request $request, Calendar $calendar, Price $price): View
    {
        $attributes = $request->session()->get('reservation', []);

        $reservation = new Reservation($attributes);

        $prices = [];

        foreach (['overnight_stay', 'cleaning_fee'] as $key) {
            $prices[$key] = $price->get(config("reservation.$key"));
        }

        return \view('home', [
            'unavailable_dates' => $calendar->unavailableDates(),
            'reservation' => $reservation,
            ...$prices,
        ]);
    }

    /**
     * @param Request $request
     * @param Calendar $calendar
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request, Calendar $calendar): RedirectResponse
    {
        $attributes = $request->validate(self::rules());

        $attributes += [
            'ulid' => Str::ulid(),
            'preparation_time' => new \DateInterval(config('reservation.preparation_time')),
            'user_id' => $request->user()->id,
            'price_list' => [
                'price_per_night' => config('reservation.price_per_night'),
                'cleaning_fee' => config('reservation.cleaning_fee'),
            ],
        ];

        $reservation = new Reservation($attributes);

        if ($calendar->isNotAvailable(...$reservation->reservedPeriod)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        $reservation->save();

        return redirect()->route('reservation.show', [$reservation]);
    }

    /**
     * @param Reservation $reservation
     * @param Calendar $calendar
     * @return View
     */
    public function show(Reservation $reservation, Calendar $calendar): View
    {
        return \view('reservation.show', [
            'unavailableDates' => $calendar->unavailableDates(),
            'reservation' => $reservation
        ]);
    }

    /**
     * @param Request $request
     * @param Reservation $reservation
     * @param Calendar $calendar
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function update(Request $request, Reservation $reservation, Calendar $calendar): RedirectResponse
    {
        // TODO: Check reservation status.

        $attributes = $request->validate(self::rules($reservation));

        $reservation->fill($attributes);

        if ($calendar->isNotAvailable(...$reservation->reservedPeriod)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        $reservation->save();

        return redirect()->route('reservation.show', [$reservation]);
    }

    /**
     * @param Reservation|null $reservation
     * @return array[]
     */
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
