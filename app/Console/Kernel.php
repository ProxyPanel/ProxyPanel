<?php

namespace App\Console;

use App\Console\Commands\AutoClearLogs;
use App\Console\Commands\DailyNodeReport;
use App\Console\Commands\NodeStatusDetection;
use App\Console\Commands\ServiceTimer;
use App\Console\Commands\TaskAuto;
use App\Console\Commands\TaskDaily;
use App\Console\Commands\TaskHourly;
use App\Console\Commands\TaskMonthly;
use App\Console\Commands\UserExpireWarning;
use App\Console\Commands\UserTrafficWarning;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        AutoClearLogs::class,
        DailyNodeReport::class,
        NodeStatusDetection::class,
        ServiceTimer::class,
        TaskAuto::class,
        TaskDaily::class,
        TaskHourly::class,
        TaskMonthly::class,
        UserExpireWarning::class,
        UserTrafficWarning::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('serviceTimer')->everyFiveMinutes();
        $schedule->command('nodeStatusDetection')->everyTenMinutes();
        $schedule->command('autoClearLogs')->everyThirtyMinutes();
        $schedule->command('task:hourly')->hourly();
        $schedule->command('task:daily')->dailyAt('02:30');
        $schedule->command('dailyNodeReport')->dailyAt('09:30');
        $schedule->command('userTrafficWarning')->dailyAt('10:30');
        $schedule->command('userExpireWarning')->dailyAt('20:30');
        $schedule->command('task:auto')->everyMinute();
        $schedule->command('task:monthly')->monthly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
