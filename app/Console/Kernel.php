<?php

namespace App\Console;

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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('autoJob')->everyMinute();
        $schedule->command('autoClearLog')->everyThirtyMinutes();
        $schedule->command('autoDecGoodsTraffic')->everyTenMinutes();
        $schedule->command('autoResetUserTraffic')->daily();
        $schedule->command('autoCheckNodeStatus')->everyMinute();
        $schedule->command('autoStatisticsNodeDailyTraffic')->dailyAt('23:55');
        $schedule->command('autoStatisticsNodeHourlyTraffic')->hourly();
        $schedule->command('autoStatisticsUserDailyTraffic')->dailyAt('23:50');
        $schedule->command('autoStatisticsUserHourlyTraffic')->hourly();
        $schedule->command('userTrafficAbnormalAutoWarning')->hourly();
        $schedule->command('userExpireAutoWarning')->dailyAt('20:00');
        $schedule->command('userTrafficAutoWarning')->dailyAt('10:30');
        $schedule->command('autoReportNode')->dailyAt('09:00');
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
