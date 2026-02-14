<?php

namespace App\View\Components;

use App\Models\Reservation;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ReservationFeed extends Component
{
    public function __construct(
        public Reservation $reservation
    ) {}

    public function render(): View
    {
        return view('components.reservation-feed');
    }
}
