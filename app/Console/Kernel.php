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

        \Log::info('Scheduler loaded from: ' . __FILE__);
        \Log::info('Kernel hash: ' . md5_file(__FILE__));
        // // Запуск команды несколько раз в день
        // $schedule->command('expiring-docs:notify')->twiceDaily(8, 15);
        // $schedule->command('expiring-docs:notify')->dailyAt('02:30');
        // $schedule->command('expiring-docs:notify')->dailyAt('10:00');
        // $schedule->command('expiring-docs:notify')->dailyAt('10:30');
        // $schedule->command('expiring-docs:notify')->dailyAt('11:00');
        // $schedule->command('expiring-docs:notify')->dailyAt('12:00');
        //  $schedule->command('expiring-docs:notify')->dailyAt('13:00');
         $schedule->command('expiring-docs:notify')->everyMinute();

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
