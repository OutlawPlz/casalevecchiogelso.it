<?php

namespace Database\Seeders;

use App\Models\Price;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Price::syncFromStripe();
        Product::syncFromStripe();

        User::factory()
            ->host()
            ->create();

        User::factory()
            ->guest()
            ->has(Reservation::factory())
            ->create();
    }
}
