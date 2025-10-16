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
        \App\Console\Commands\SendExpiringDocsNotifications::class,
    ];

    /**
     * Определяет планировщик команд.
     */
    protected function schedule(Schedule $schedule)
    {
        // Запуск команды несколько раз в день
        $schedule->command('expiring-docs:notify')->twiceDaily(8, 15);
        $schedule->command('expiring-docs:notify')->dailyAt('12:30');
        $schedule->command('expiring-docs:notify')->dailyAt('14:00');
        $schedule->command('expiring-docs:notify')->dailyAt('23:50');
        $schedule->command('expiring-docs:notify')->dailyAt('23:52');
        $schedule->command('expiring-docs:notify')->dailyAt('23:54');

        \Log::info('✅ Scheduler is running fine: ' . now());
    }

    /**
     * Регистрация всех команд Artisan.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        parent::commands();
    }
}
