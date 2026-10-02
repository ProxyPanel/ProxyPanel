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
        // 全部任务都防止重叠：上一轮没跑完时不再启动下一轮
        // （task:auto / hysteria2:sync 每分钟一次，一轮卡住就会不断叠加）
        // 比日更低频的任务显式指定锁过期时间：默认的 1440 分钟会让一次卡住的任务停摆一整天
        $schedule->command('serviceTimer')->everyFiveMinutes()->withoutOverlapping(10);
        $schedule->command('node:detection')->everyTenMinutes()->withoutOverlapping(60);
        // 日志清理拆成两档：心跳的保留期只有 30 分钟，必须高频；其余大表挪到凌晨低峰，避开 20:30 那一拨任务
        // 04:07 刻意避开 5/10/30 分钟的整点网格（serviceTimer、node:detection、下面这档 --light 都在那些整点上）
        $schedule->command('autoClearLogs --light')->everyThirtyMinutes()->withoutOverlapping(10);
        $schedule->command('autoClearLogs')->dailyAt('04:07')->withoutOverlapping();
        // 只清 personal_access_tokens.expires_at 有值的过期行
        $schedule->command('sanctum:prune-expired --hours=24')->daily()->withoutOverlapping();
        $schedule->command('task:hourly')->hourly()->withoutOverlapping(120);
        $schedule->command('task:daily')->dailyAt('00:05')->withoutOverlapping();
        $schedule->command('node:maintenance')->dailyAt('09:30')->withoutOverlapping();
        $schedule->command('userTrafficWarning')->dailyAt('10:30')->withoutOverlapping();
        $schedule->command('userExpireWarning')->dailyAt('20:30')->withoutOverlapping();
        $schedule->command('task:auto')->everyMinute()->withoutOverlapping(5);
        $schedule->command('task:monthly')->monthly()->withoutOverlapping();
        $schedule->command('hysteria2:sync')->everyMinute()->withoutOverlapping(5);
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
