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
    /**
     * @param Request $request
     * @param Calendar $calendar
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request, Calendar $calendar): RedirectResponse
    {
        $authUser = $request->user();

        $reservation = Reservation::fromSession();

        $reservation->fill([
            'ulid' => Str::ulid(),
            'name' => $authUser->name,
            'email' => $authUser->email,
            'preparation_time' => new \DateInterval(config('reservation.preparation_time')),
            'user_id' => $authUser->id,
            'price_list' => [
                config('reservation.overnight_stay') => $reservation->nights,
                config('reservation.cleaning_fee') => 1,
            ]
        ]);

        // Updates calendar events
        // before creating a reservation.
        $calendar->syncFromServices();

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
     * @return View
     */
    public function show(Reservation $reservation): View
    {
        return view('reservation.show', [$reservation]);
    }
}
