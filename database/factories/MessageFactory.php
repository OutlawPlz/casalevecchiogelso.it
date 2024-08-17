<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $message = $this->faker->sentence();

        return [
            'user_id' => null,
            'reservation_id' => null,
            'channel' => '01J3JCD85NEBYG538WKEEJCYP9',
            'author' => [
                'name' => 'John Doe',
                'email' => 'john@doe.com',
            ],
            'content' => [
                'raw' => $message,
                'it' => $message,
                'en' => $message,
            ],
            'media' => [],
        ];
    }

    /**
     * @return $this
     */
    public function reply(): static
    {
        $message = $this->faker->sentence();

        return $this->state(fn (array $attributes) => [
            'author' => [
                'name' => 'Jane Doe',
                'email' => 'jane@doe.com',
            ],
            'content' => [
                'raw' => $message,
                'it' => $message,
                'en' => $message,
            ],
        ]);
    }
}
