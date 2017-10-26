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
        \App\Console\Commands\AutoDecGoodsTrafficJob::class,
        \App\Console\Commands\AutoDisableExpireUserJob::class,
        \App\Console\Commands\AutoExpireCouponJob::class,
        \App\Console\Commands\AutoExpireInviteJob::class,
        //\App\Console\Commands\AutoGetLocationInfoJob::class,
        \App\Console\Commands\AutoResetUserTrafficJob::class,
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
        $schedule->command('command:autoDecGoodsTrafficJob')->everyTenMinutes();
        $schedule->command('command:autoDisableExpireUserJob')->everyMinute();
        $schedule->command('command:autoExpireCouponJob')->everyThirtyMinutes();
        $schedule->command('command:autoExpireInviteJob')->everyThirtyMinutes();
        //$schedule->command('command:autoGetLocationInfoJob')->everyMinute();
        $schedule->command('command:autoResetUserTrafficJob')->monthly();
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
