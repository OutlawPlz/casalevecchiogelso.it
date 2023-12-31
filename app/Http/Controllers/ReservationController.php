<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\Calendar;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    /**
     * @param Request $request
     * @return void
     */
    public function store(Request $request, Calendar $calendar): void
    {
        $attributes = $request->validate(self::rules());

        $attributes += [
            'preparation_time' => config('reservation.preparation_time'),
//            'status' => 'pending'
        ];

        $reservation = new Reservation($attributes);

        if ($calendar->isNotAvailable(...$reservation->reserved_period)) {
            throw ValidationException::withMessages([
                // TODO: Add validation message...
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
            'phone' => ['required'],
            'check_in' => ['required', 'date', 'after:tomorrow'],
            'check_out' => ['required', 'date', 'after:start_date'],
            'guests_count' => ['required', 'digits_between:1,10']
        ];
    }
}
