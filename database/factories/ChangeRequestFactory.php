<?php

namespace Database\Factories;

use App\Enums\ChangeRequestStatus;
use App\Models\ChangeRequest;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChangeRequest>
 */
class ChangeRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'user_id' => User::factory(),
            'from' => function (array $attributes) {
                $reservation = Reservation::query()->find($attributes['reservation_id']);

                return [
                    'check_in' => $reservation->check_in,
                    'check_out' => $reservation->check_out,
                    'guest_count' => $reservation->guest_count,
                    'price_list' => $reservation->price_list,
                ];
            },
            'to' => function (array $attributes) {
                $reservation = Reservation::query()->find($attributes['reservation_id']);

                $priceList = $reservation->price_list;

                $priceList[0]['quantity'] = 6;

                return [
                    'check_in' => $reservation->check_in,
                    'check_out' => $reservation->check_out->subDay(),
                    'guest_count' => $reservation->guest_count,
                    'price_list' => $priceList,
                ];
            },
            'reason' => $this->faker->sentence(),
            'status' => ChangeRequestStatus::PENDING,
        ];
    }
}
