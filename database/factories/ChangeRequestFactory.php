<?php

namespace Database\Factories;

use App\Enums\ChangeRequestStatus;
use App\Models\ChangeRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChangeRequest>
 */
class ChangeRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'check_in' => today()->addWeek(),
            'check_out' => today()->addWeeks(2),
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
            'status' => ChangeRequestStatus::DRAFT,
        ];
    }
}
