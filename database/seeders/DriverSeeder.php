<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            ['first' => 'Janis', 'last' => 'Berzins', 'phone' => '+37120000001'],
            ['first' => 'Pavel', 'last' => 'Ivanov', 'phone' => '+37120000002'],
            ['first' => 'Arturs', 'last' => 'Kalnins', 'phone' => '+37120000003'],
        ];

        foreach ($drivers as $info) {

            // Генерация уникального PIN
            $pin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            // User
            $user = User::updateOrCreate(
                ['email' => strtolower($info['first']).'.'.strtolower($info['last']).'@fleet.test'],
                [
                    'name' => $info['first'].' '.$info['last'],
                    'password' => Hash::make('driver123'),
                    'role' => 'driver',
                ]
            );

            // Driver
            Driver::updateOrCreate(
                ['phone' => $info['phone']], // условие поиска
                [
                    'first_name'  => $info['first'],
                    'last_name'   => $info['last'],
                    'user_id'     => $user->id,
                    'login_pin'   => $pin,
                    'status'      => 1,
                    'is_active'   => 1,

                    // ОБЯЗАТЕЛЬНЫЕ ПОЛЯ — иначе SQLSTATE 1364
                    'license_number' => 'TEST' . random_int(1000, 9999),
                    'license_issued' => '2020-01-01',
                    'license_end'    => '2030-01-01',
                ]
            );

            echo "Driver {$info['first']} {$info['last']} — PIN: {$pin}\n";
        }
    }
}
