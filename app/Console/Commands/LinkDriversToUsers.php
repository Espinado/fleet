<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Str;

class LinkDriversToUsers extends Command
{
    protected $signature = 'drivers:link-users';
    protected $description = 'Create unique users for each driver and link them';

    public function handle()
    {
        $drivers = Driver::all();

        foreach ($drivers as $driver) {

            // если уже связан — пропускаем
            if ($driver->user_id && User::find($driver->user_id)) {
                $this->info("Driver {$driver->id} already linked → User {$driver->user_id}");
                continue;
            }

            // создаём нового пользователя
            $user = User::create([
                'name' => $driver->first_name . ' ' . $driver->last_name,
                'email' => Str::uuid() . '@driver.local',
                'password' => bcrypt(Str::random(12)),
                'role' => 'driver',
            ]);

            // привязываем водителя
            $driver->user_id = $user->id;
            $driver->save();

            $this->info("Linked driver {$driver->id} → User {$user->id}");
        }

        $this->info("Done!");
    }
}
