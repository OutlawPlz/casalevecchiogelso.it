<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ReservationFeedController extends Controller
{
    public function __invoke(Request $request, Reservation $reservation): Collection
    {
        return Activity::query()
            ->whereHasMorph(
                'subject',
                Reservation::class,
                fn ($query) => $query->where('id', $reservation->id)
            )
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
