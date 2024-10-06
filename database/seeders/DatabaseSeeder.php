<?php

namespace Database\Seeders;

use App\Models\Price;
use App\Models\Product;
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
        User::factory()->create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
        ]);

        User::factory()->create([
            'name' => 'Host User',
            'email' => 'host@example.com',
        ]);

        Product::syncFromStripe();

        Price::syncFromStripe();
    }
}
