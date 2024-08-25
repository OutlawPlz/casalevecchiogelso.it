<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Services\Calendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    /**
     * @param  Request  $request
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $reservations = Reservation::query();

        if ($authUser->isGuest()) {
            $reservations->where('user_id', $authUser->id);
        }

        $reservations = $reservations->simplePaginate();

        return view('reservation.index', [
            'authUser' => $authUser,
            'reservations' => $reservations,
        ]);
    }

    /**
     * @param  Request  $request
     * @param  Calendar  $calendar
     * @return RedirectResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function store(Request $request, Calendar $calendar): RedirectResponse
    {
        $reservation = Reservation::fromSession();

        Validator::make(
            $reservation->toArray(),
            ReservationQuoteController::rules()
        )->validate();

        $priceList = Product::defaultPriceList();

        array_walk($priceList, function (&$line) use ($reservation) {
            if (is_overnight_stay($line['product'])) $line['quantity'] = $reservation->nights;
        });

        $authUser = $request->user();

        $reservation->fill([
            'ulid' => Str::ulid(),
            'name' => $authUser->name,
            'email' => $authUser->email,
            'preparation_time' => new \DateInterval(config('reservation.preparation_time')),
            'user_id' => $authUser->id,
            'price_list' => $priceList
        ]);

        $calendar->sync();

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
     * @param  Request  $request
     * @param  Reservation  $reservation
     * @return View
     */
    public function show(Request $request, Reservation $reservation): View
    {
        /** @var User $authUser */
        $authUser = $request->user();

        return view('reservation.show', [
            'authUser' => $authUser,
            'reservation' => $reservation,
        ]);
    }
}
