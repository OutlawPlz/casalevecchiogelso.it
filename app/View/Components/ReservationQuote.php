<?php

namespace App\View\Components;

use App\Models\Reservation;
use App\Services\Calendar;
use App\Services\Price;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ReservationQuote extends Component
{
    public array $unavailable_dates;

    public int $overnight_stay;

    public int $cleaning_fee;

    public Reservation $reservation;

    /**
     * @param Price $price
     * @param Calendar $calendar
     * @throws \Exception
     */
    public function __construct(Price $price, Calendar $calendar)
    {
        $this->unavailable_dates = $calendar->unavailableDates();

        $this->overnight_stay = $price->get(config("reservation.overnight_stay"))['unit_amount'];

        $this->cleaning_fee = $price->get(config("reservation.cleaning_fee"))['unit_amount'];

        $this->reservation = Reservation::fromSession();
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('components.reservation-quote');
    }
}
