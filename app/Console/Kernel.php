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
        // Для теста запускаем каждую минуту
        $schedule->command('expiring-docs:send')->everyMinute();

        // Для реального использования можно так:
        // $schedule->command('expiring-docs:send')->dailyAt('08:00');
        // $schedule->command('expiring-docs:send')->dailyAt('14:00');
        // $schedule->command('expiring-docs:send')->dailyAt('20:00');
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
