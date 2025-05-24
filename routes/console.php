<?php

use App\Actions\ChargeOnDueDate;
use App\Actions\RequestPayout;
use App\Models\Reservation;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $reservations = Reservation::query()
        ->where('check_in', today())
        ->get();

    foreach ($reservations as $reservation) {
        (new RequestPayout)($reservation);
    }
})->daily();

Schedule::call(new ChargeOnDueDate)->daily();
