<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakePushRecipientUser extends Command
{
    protected $signature = 'make:push-recipient
                            {--email= : Email (default: from config)}';
    protected $description = 'Create user for push notifications recipient (e.g. rvr@arguss.lv)';

    public function handle(): int
    {
        $email = $this->option('email') ?: config('notifications.push_recipient_email', 'rvr@arguss.lv');

        if (User::where('email', $email)->exists()) {
            $this->info("Пользователь с email {$email} уже существует. Войдите в админку и включите уведомления.");
            return 0;
        }

        $password = str()->random(16);
        $user = User::create([
            'name'     => 'Push recipient',
            'email'    => $email,
            'password' => Hash::make($password),
        ]);
        if (\Schema::hasColumn((new User)->getTable(), 'role')) {
            $user->update(['role' => 'admin']);
        }

        $this->info("Создан пользователь: {$email}");
        $this->line('Пароль (сохраните и смените после входа): ' . $password);
        $this->newLine();
        $this->comment('Дальше: войдите в админку под этим пользователем и нажмите «Включить уведомления» — после этого пуши будут приходить даже при закрытом приложении.');

        return 0;
    }
}
