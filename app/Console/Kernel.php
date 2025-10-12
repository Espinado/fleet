<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Зарегистрированные Artisan команды.
     */
    protected $commands = [
        \App\Console\Commands\SendExpiringDocsEmails::class,
    ];

    /**
     * Определяет планировщик команд.
     */
    protected function schedule(Schedule $schedule)
{
    // 3 раза в день: 8:00, 14:00, 20:00
    $schedule->command('expiring-docs:notify')->twiceDaily(8, 14);
    $schedule->command('expiring-docs:notify')->dailyAt('20:00');
}

    /**
     * Регистрация всех команд Artisan.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        parent::commands(); // обязательно вызывать
    }
}
