<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ReservationFeedController extends Controller
{
    /**
     * @param  Request  $request
     * @param  Reservation  $reservation
     * @return Paginator
     */
    public function __invoke(Request $request, Reservation $reservation): Paginator
    {
        return Activity::query()
            ->whereHasMorph(
                'subject',
                Reservation::class,
                fn ($query) => $query->where('id', $reservation->id)
            )
            ->simplePaginate();
    }
}
