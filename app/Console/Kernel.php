<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('serviceTimer')->everyFiveMinutes();
        $schedule->command('nodeStatusDetection')->everyTenMinutes();
        $schedule->command('autoClearLogs')->everyThirtyMinutes();
        $schedule->command('task:hourly')->hourly();
        $schedule->command('task:daily')->dailyAt('00:05');
        $schedule->command('dailyNodeReport')->dailyAt('09:30');
        $schedule->command('userTrafficWarning')->dailyAt('10:30');
        $schedule->command('userExpireWarning')->dailyAt('20:30');
        $schedule->command('task:auto')->everyMinute();
        $schedule->command('task:monthly')->monthly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
