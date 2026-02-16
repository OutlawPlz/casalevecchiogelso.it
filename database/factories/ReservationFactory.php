<?php

namespace Database\Factories;

use App\Enums\CancellationPolicy;
use App\Enums\ReservationStatus;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
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
            'user_id' => User::factory(),
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

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::CONFIRMED,
            'check_in' => now()->subDays(4),
            'check_out' => now()->addDays(3),
        ])->afterCreating(function (Reservation $reservation) {
            Payment::factory()->create([
                'reservation_ulid' => $reservation->ulid,
                'amount' => $reservation->tot,
            ]);
        });
    }

    public function inRefundPeriod(): static
    {
        return $this->state(function (array $attributes) {
            $cancellationPolicy = CancellationPolicy::from($attributes['cancellation_policy']);

            return [
                'status' => ReservationStatus::CONFIRMED,
                'check_in' => now()->add($cancellationPolicy->timeWindow()),
                'check_out' => now()->add($cancellationPolicy->timeWindow())->addWeek(),
            ];
        })->afterCreating(function (Reservation $reservation) {
            Payment::factory()->create([
                'reservation_ulid' => $reservation->ulid,
                'amount' => $reservation->tot,
            ]);
        });
    }
}
