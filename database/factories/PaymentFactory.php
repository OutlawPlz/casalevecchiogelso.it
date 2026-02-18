<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer' => 'cus_SLrr1mYs4mcFTJ',
            'payment_intent' => 'pi_3RMEFLAKSJP4UmE20jY687Vr',
            'amount' => 100,
            'status' => 'succeeded',
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'payment_failed',
        ]);
    }
}
