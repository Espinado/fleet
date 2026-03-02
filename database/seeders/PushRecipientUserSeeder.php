<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PushRecipientUserSeeder extends Seeder
{
    /**
     * Пользователь для пуш-уведомлений (rvr@arguss.lv), как копия админа — с подтверждённым email.
     */
    public function run(): void
    {
        $data = [
            'name'              => 'Push Recipient',
            'password'          => Hash::make('12345'),
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn((new User)->getTable(), 'role')) {
            $data['role'] = 'admin';
        }

        User::updateOrCreate(
            ['email' => 'rvr@arguss.lv'],
            $data
        );
    }
}
