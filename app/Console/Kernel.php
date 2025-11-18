<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Тестовая метка — должна появляться в логе КАЖДУЮ минуту
        \Log::info('⚡ schedule() called at: ' . now());

           $schedule->command('expiring-docs:notify')->dailyAt('09:00');
    $schedule->command('expiring-docs:notify')->dailyAt('11:00');
    $schedule->command('expiring-docs:notify')->dailyAt('12:00');
    $schedule->command('expiring-docs:notify')->dailyAt('21:15');
    $schedule->command('expiring-docs:notify')->dailyAt('21:30');

        \Log::info('🚀 schedule() finished at: ' . now());
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        parent::commands();
    }
}
