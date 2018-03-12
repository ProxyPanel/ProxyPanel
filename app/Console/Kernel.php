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
        \App\Console\Commands\AutoDisableUserJob::class,
        \App\Console\Commands\AutoExpireCouponJob::class,
        \App\Console\Commands\AutoExpireInviteJob::class,
        \App\Console\Commands\AutoReleasePortJob::class,
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
        $schedule->command('autoBanSubscribeJob')->everyThirtyMinutes();
        $schedule->command('autoBanUserJob')->everyTenMinutes();
        $schedule->command('autoCheckNodeStatusJob')->everyMinute();
        $schedule->command('autoClearLogJob')->everyThirtyMinutes();
        $schedule->command('autoDecGoodsTrafficJob')->everyTenMinutes();
        $schedule->command('autoDisableExpireUserJob')->everyMinute();
        $schedule->command('autoDisableUserJob')->everyMinute();
        $schedule->command('autoExpireCouponJob')->everyThirtyMinutes();
        $schedule->command('autoExpireInviteJob')->everyThirtyMinutes();
        $schedule->command('autoReleasePortJob')->everyMinute();
        $schedule->command('autoReopenUserJob')->everyMinute();
        $schedule->command('autoResetUserTrafficJob')->everyFiveMinutes();
        $schedule->command('autoStatisticsNodeDailyTrafficJob')->dailyAt('04:30');
        $schedule->command('autoStatisticsNodeHourlyTrafficJob')->hourly();
        $schedule->command('autoStatisticsUserDailyTrafficJob')->dailyAt('03:00');
        $schedule->command('autoStatisticsUserHourlyTrafficJob')->hourly();
        $schedule->command('userExpireWarningJob')->daily();
        $schedule->command('userTrafficWarningJob')->daily();
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
