<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'sovsem@deneg.net'],
            [
                'name' => 'Vini Puh',
                'password' => Hash::make('KakUkrastjMillion'), // можно поменять позже
                'email_verified_at' => now(),
            ]
        );
    }
}
