<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\Calendar;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReservationQuoteController extends Controller
{
    /**
     * @param Request $request
     * @param Calendar $calendar
     * @return void
     */
    public function __invoke(Request $request, Calendar $calendar): void
    {
        $attributes = $request->validate(self::rules());

        $reservation = new Reservation($attributes);

        if ($calendar->isNotAvailable(...$reservation->reservedPeriod)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        $reservation->toSession();
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        $date = now()->addDays(2)->format('Y-m-d');

        return [
            'check_in' => ['required', 'date', "after:$date"],
            'check_out' => ['required', 'date', 'after:start_date'],
            'guest_count' => ['required','numeric', 'min:1', 'max:10']
        ];
    }
}
