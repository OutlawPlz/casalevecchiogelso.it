<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function host(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Host User',
            'email' => 'host@example.com',
            'role' => 'host',
            'stripe_id' => 'cus_SLrqNkAazJqCMR',
        ]);
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'stripe_id' => 'cus_SLrr1mYs4mcFTJ',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
