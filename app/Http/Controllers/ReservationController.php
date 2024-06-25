<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Reservation;
use App\Services\Calendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
                'overnight_stay' => config('reservation.overnight_stay'),
                'cleaning_fee' => config('reservation.cleaning_fee'),
            ]
        ]);

        // Updates calendar events
        // before creating a reservation.
        $calendar->sync();

        // TODO: Il component x-reservation-quote dovrebbe ricevere
        // questo errore come parametro e gestirlo attraverso Alpine...
        if ($calendar->isNotAvailable(...$reservation->reservedPeriod)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        $reservation->save();

        session()->forget('reservation');

        return redirect()->route('reservation.show', [$reservation]);
    }

    /**
     * @param Reservation $reservation
     * @return View
     */
    public function show(Reservation $reservation): View
    {
        $chat = $reservation
            ->messages()
            ->limit(30)
            ->get()
            ->groupBy(
                fn (Message $message) => $message->created_at->format('Y-m-d')
            );

        return view('reservation.show', [
            'reservation' => $reservation,
            'chat' => $chat,
        ]);
    }
}
