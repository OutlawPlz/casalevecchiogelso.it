<?php

namespace Database\Factories;

use App\Models\Price;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stripe_id' => 'prod_'.fake()->regexify('[A-Za-z0-9]{14}'),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'default_price' => 'price_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            Price::factory()->create([
                'stripe_id' => $product->default_price,
                'product' => $product->stripe_id,
            ]);
        });
    }

    public function overnightStay(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Overnight Stay',
            'stripe_id' => config('reservation.overnight_stay'),
        ]);
    }
}
