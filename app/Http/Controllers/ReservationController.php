<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\Calendar;
use App\Services\Price;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    protected array $overnightStay;

    protected array $cleaningFee;

    public function __construct()
    {
        $prices = Storage::json('prices.json') ?? [];

        $overnightStayIndex = array_search(
            config('reservation.overnight_stay'),
            array_column($prices, 'id')
        );

        if ($overnightStayIndex === false) {
            throw new \Exception('The price "overnight_stay" not found in prices.json file.');
        }

        $this->overnightStay = $prices[$overnightStayIndex];

        $cleaningFeeIndex = array_search(
            config('reservation.cleaning_fee'),
            array_column($prices, 'id')
        );

        if ($overnightStayIndex === false) {
            throw new \Exception('The price "cleaning_fee" not found in prices.json file.');
        }

        $this->cleaningFee = $prices[$cleaningFeeIndex];
    }

    /**
     * @param string ...$priceKeys
     * @return array
     * @throws \Exception
     */
    protected function getPrices(string ...$priceKeys): array
    {
        $prices = Storage::json('prices.json');

        if (! $prices) {
            throw new \Exception('The prices.json file is empty. You should sync local prices with Stripe prices.');
        }

        foreach ($priceKeys as $priceKey) {
            $index = array_search(
                config("reservation.$priceKey"),
                array_column($prices, 'id')
            );

            if ($index === false) {
                throw new \Exception("The price \"$priceKey\" not found in prices.json file.");
            }


        }
    }

    /**
     * @param Request $request
     * @param Calendar $calendar
     * @return View
     */
    public function create(Request $request, Calendar $calendar, Price $price): View
    {
        $reservation = new Reservation([
            'check_id' => $request->get('check_id'),
            'check_out' => $request->get('check_out'),
            'guest_count' => $request->get('guest_count'),
        ]);

        return \view('reservation.create', [
            'unavailableDates' => $calendar->unavailableDates(),
            'reservation' => $reservation,
            'overnightStay' => $price->get(config('reservation.overnight_stay')),
            'cleaningFee' => $price->get(config('reservation.cleaning_fee')),
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
