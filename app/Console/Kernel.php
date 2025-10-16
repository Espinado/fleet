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

    protected $signature = 'expiring-docs:notify';

    /**
     * Определяет планировщик команд.
     */
    protected function schedule(Schedule $schedule)
{
    // 3 раза в день: 8:00, 14:00, 20:00
    $schedule->command('expiring-docs:notify')->twiceDaily(8, 15);
    $schedule->command('expiring-docs:notify')->dailyAt('12:30');
    $schedule->command('expiring-docs:notify')->dailyAt('14:00');
    $schedule->command('expiring-docs:notify')->dailyAt('23:10');
    $schedule->command('expiring-docs:notify')->dailyAt('23:25');
    $schedule->command('expiring-docs:notify')->dailyAt('23:30');
      \Log::info('✅ Scheduler is running fine: ' . now());

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
