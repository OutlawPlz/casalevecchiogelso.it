<?php

namespace Database\Factories;

use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Price>
 */
class PriceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stripe_id' => 'price_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'currency' => 'eur',
            'unit_amount' => fake()->numberBetween(1000, 100000),
            'product' => 'prod_'.fake()->regexify('[A-Za-z0-9]{14}'),
            'active' => true,
        ];
    }
}
