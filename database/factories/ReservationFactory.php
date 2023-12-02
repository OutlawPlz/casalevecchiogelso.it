<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uid' => Str::ulid(),
            'check_in' => today(),
            'check_out' => today()->addWeek(),
            'guests_count' => 7,
            'preparation_time' => new \DateInterval('P1D')
        ];
    }
}
