<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Matteo',
            'email' => 'matteo.palazzo@outlook.com',
            'password' => Hash::make('password'),
            'role' => UserRole::HOST,
            'email_verified_at' => now(),
        ]);
    }
}
