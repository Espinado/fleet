<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'email' => 'sovsem@deneg.net',
                'name'  => 'Vini Puh',
                'password' => 'KakUkrastjMillion',
            ],
            [
                'email' => 'admin@test.lv',
                'name'  => 'Admin',
                'password' => 'DerParolen',
            ],
        ];

        foreach ($admins as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                    'role' => 'admin',
                ]
            );
        }

        $this->command->info('Created ' . count($admins) . ' admin users.');
    }
}
