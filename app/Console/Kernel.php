<?php

namespace App\Console;

use App\Console\Commands\AutoClearLogs;
use App\Console\Commands\AutoJob;
use App\Console\Commands\DailyJob;
use App\Console\Commands\DailyNodeReport;
use App\Console\Commands\NodeDailyTrafficStatistics;
use App\Console\Commands\NodeHourlyTrafficStatistics;
use App\Console\Commands\NodeStatusDetection;
use App\Console\Commands\ServiceTimer;
use App\Console\Commands\UserDailyTrafficStatistics;
use App\Console\Commands\UserExpireWarning;
use App\Console\Commands\UserHourlyTrafficMonitoring;
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
        AutoJob::class,
        DailyJob::class,
        DailyNodeReport::class,
        NodeDailyTrafficStatistics::class,
        NodeHourlyTrafficStatistics::class,
        NodeStatusDetection::class,
        ServiceTimer::class,
        UserDailyTrafficStatistics::class,
        UserExpireWarning::class,
        UserHourlyTrafficMonitoring::class,
        UserTrafficWarning::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('serviceTimer')->everyTenMinutes();
        $schedule->command('nodeStatusDetection')->everyTenMinutes();
        $schedule->command('autoClearLogs')->everyThirtyMinutes();
        $schedule->command('nodeHourlyTrafficStatistics')->hourly();
        $schedule->command('userHourlyTrafficMonitoring')->hourly();
        $schedule->command('dailyJob')->daily();
        $schedule->command('dailyNodeReport')->dailyAt('09:00');
        $schedule->command('userTrafficWarning')->dailyAt('10:30');
        $schedule->command('userExpireWarning')->dailyAt('20:00');
        $schedule->command('userDailyTrafficStatistics')->daily();
        $schedule->command('nodeDailyTrafficStatistics')->daily();
        $schedule->command('autoJob')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
