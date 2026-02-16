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
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer' => 'cus_1234567890',
            'payment_intent' => 'pi_1234567890',
            'amount' => 100,
            'status' => 'succeeded',
        ];
    }
}
