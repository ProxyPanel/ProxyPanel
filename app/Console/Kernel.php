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
        \App\Console\Commands\AutoBanSubscribeJob::class,
        \App\Console\Commands\AutoBanUserJob::class,
        \App\Console\Commands\AutoCheckNodeStatusJob::class,
        \App\Console\Commands\AutoClearLogJob::class,
        \App\Console\Commands\AutoDecGoodsTrafficJob::class,
        \App\Console\Commands\AutoDisableExpireUserJob::class,
        \App\Console\Commands\AutoExpireCouponJob::class,
        \App\Console\Commands\AutoExpireInviteJob::class,
        \App\Console\Commands\AutoGetLocationInfoJob::class,
        \App\Console\Commands\AutoReopenUserJob::class,
        \App\Console\Commands\AutoResetUserTrafficJob::class,
        \App\Console\Commands\AutoStatisticsNodeDailyTrafficJob::class,
        \App\Console\Commands\AutoStatisticsNodeHourlyTrafficJob::class,
        \App\Console\Commands\AutoStatisticsUserDailyTrafficJob::class,
        \App\Console\Commands\AutoStatisticsUserHourlyTrafficJob::class,
        \App\Console\Commands\UserExpireWarningJob::class,
        \App\Console\Commands\UserTrafficWarningJob::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('command:autoBanSubscribeJob')->everyThirtyMinutes();
        $schedule->command('command:autoBanUserJob')->everyTenMinutes();
        $schedule->command('command:autoCheckNodeStatusJob')->everyMinute();
        $schedule->command('command:autoClearLogJob')->everyThirtyMinutes();
        $schedule->command('command:autoDecGoodsTrafficJob')->everyTenMinutes();
        $schedule->command('command:autoDisableExpireUserJob')->everyMinute();
        $schedule->command('command:autoExpireCouponJob')->everyThirtyMinutes();
        $schedule->command('command:autoExpireInviteJob')->everyThirtyMinutes();
        //$schedule->command('command:autoGetLocationInfoJob')->everyMinute();
        $schedule->command('command:autoReopenUserJob')->everyMinute();
        $schedule->command('command:autoResetUserTrafficJob')->everyFiveMinutes();
        $schedule->command('command:autoStatisticsNodeDailyTrafficJob')->dailyAt('04:30');
        $schedule->command('command:autoStatisticsNodeHourlyTrafficJob')->hourly();
        $schedule->command('command:autoStatisticsUserDailyTrafficJob')->dailyAt('03:00');
        $schedule->command('command:autoStatisticsUserHourlyTrafficJob')->hourly();
        $schedule->command('command:userExpireWarningJob')->daily();
        $schedule->command('command:userTrafficWarningJob')->daily();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
