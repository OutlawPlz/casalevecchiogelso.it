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

    public function __construct(Price $price, Calendar $calendar)
    {
        $this->unavailable_dates = $calendar->unavailableDates();

        foreach (['overnight_stay', 'cleaning_fee'] as $key) {
            $this->$key = $price->get(config("reservation.$key"))['unit_amount'];
        }

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
