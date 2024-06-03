<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\Calendar;
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

    /**
     * @param Request $request
     * @param Calendar $calendar
     * @return void
     * @throws ValidationException
     */
    public function store(Request $request, Calendar $calendar): void
    {
        $attributes = $request->validate(self::rules());

        $attributes += [
            'ulid' => Str::ulid(),
            'preparation_time' => new \DateInterval(config('reservation.preparation_time')),
//            'status' => 'pending'
        ];

        $reservation = new Reservation($attributes);

        if ($calendar->isNotAvailable(...$reservation->reserved_period)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        $reservation->save();
    }

    /**
     * @return array[]
     */
    public static function rules(): array
    {
        return [
            'first_name' => ['required', 'min:3', 'max:255'],
            'last_name' => ['required', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'check_in' => ['required', 'date', 'after:tomorrow'],
            'check_out' => ['required', 'date', 'after:start_date'],
            'guest_count' => ['required', 'digits_between:1,10']
        ];
    }
}
