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
        \App\Console\Commands\DisableExpireUserJob::class,
        \App\Console\Commands\AutoDecGoodsTrafficJob::class,
        \App\Console\Commands\UserTrafficWarningJob::class,
        \App\Console\Commands\UserExpireWarningJob::class,
        \App\Console\Commands\InviteExpire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('command:disableExpireUserJob')->everyMinute();
        $schedule->command('command:autoDecGoodsTrafficJob')->everyTenMinutes();
        $schedule->command('command:userTrafficWarningJob')->daily();
        $schedule->command('command:userExpireWarningJob')->daily();
        $schedule->command('command:inviteExpire')->everyThirtyMinutes();
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
