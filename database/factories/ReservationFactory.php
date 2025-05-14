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
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ulid' => Str::ulid(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'guest_count' => fake()->numberBetween(1, 10),
            'check_in' => today()->addWeek(),
            'check_out' => today()->addWeeks(2),
            'summary' => fake()->sentence(),
            'due_date' => today()->addWeeks(2)->sub('5 days'),
            'price_list' => [
                [
                    'product' => 'prod_QFGF5ANGoEMpOI',
                    'name' => 'Overnight stay',
                    'description' => fake()->sentence(),
                    'price' => 'price_1POlisAKSJP4UmE2U0xe8DXq',
                    'unit_amount' => 25000,
                    'quantity' => 7,
                ],
            ],
            'status' => 'confirmed',
            'cancellation_policy' => 'moderate',
        ];
    }
}
