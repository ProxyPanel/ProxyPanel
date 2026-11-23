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
        // 锁 TTL 只在异常死亡没释放锁时起作用，判据一条：显著小于任务周期，又要大于正常耗时
        // 不带参数的 withoutOverlapping 是 1440 分钟，对日级任务恰好等于周期——锁到期时刻和下一次 tick 是同一秒
        $schedule->command('serviceTimer')->everyFiveMinutes()->withoutOverlapping(10);
        $schedule->command('node:detection')->everyTenMinutes()->withoutOverlapping(60);
        // 对账只做用户增删、不 reload，所以敢定时跑：推送丢一次时靠它把已禁用或到期的账号从节点上踢掉
        $schedule->command('vnet:reconcile')->everyTenMinutes()->withoutOverlapping(9);
        // 日志清理只留一档，跑在凌晨低峰：这个点既不在 5/10/30 分钟的整点网格上，也排在库备份之后
        // （备份从 04:00 起连读约 7 分钟，两边压在同一个窗口就是让最热的两张表同时挨打）
        // 单轮要删掉一整天的量（保留期收短的那几天是千万行级），所以给六小时而不是两小时
        $schedule->command('autoClearLogs')->dailyAt('04:22')->withoutOverlapping(360);
        // 只清 personal_access_tokens.expires_at 有值的过期行
        $schedule->command('sanctum:prune-expired --hours=24')->daily()->withoutOverlapping(120);
        // 失败任务表没有其它清理路径，而每行都带完整 payload 与堆栈（实测平均 30KB）；排在清理之后
        $schedule->command('queue:prune-failed --hours=168')->dailyAt('04:27')->withoutOverlapping(120);
        // 错开到 :13，避开整点上 serviceTimer/node:detection/vnet:reconcile 的惊群；窗口仍取上一个整点小时
        // 锁 TTL 只在异常死亡没释放时起作用：要小于周期才不会挡掉下一次（同 vnet:reconcile 的 9），又远大于几十秒的正常耗时
        $schedule->command('task:hourly')->hourlyAt(13)->withoutOverlapping(50);
        $schedule->command('task:daily')->dailyAt('00:05')->withoutOverlapping(120);
        $schedule->command('node:maintenance')->dailyAt('09:30')->withoutOverlapping(120);
        $schedule->command('userTrafficWarning')->dailyAt('10:30')->withoutOverlapping(120);
        $schedule->command('userExpireWarning')->dailyAt('20:30')->withoutOverlapping(120);
        $schedule->command('task:auto')->everyMinute()->withoutOverlapping(5);
        $schedule->command('task:monthly')->monthly()->withoutOverlapping(1440);
        $schedule->command('hysteria2:sync')->everyMinute()->withoutOverlapping(5);

        $schedule->command('jangopay:reconcile --limit=10')->everyFiveMinutes()->withoutOverlapping(120);

        $this->persistOutput($schedule);
    }

    /** CLI 的 fatal 只在 stderr，落盘才有死因可查；覆盖写，被跳过的那轮不会冲掉上一轮的输出 */
    private function persistOutput(Schedule $schedule): void
    {
        foreach ($schedule->events() as $event) {
            // 命令串里的 php/artisan 路径按平台加引号，只有尾部的签名两边形态一致
            preg_match('/([a-z0-9:_-]+(?:\s+--\S+)*)$/i', $event->command, $matches);
            $slug = preg_replace('/[^A-Za-z0-9_-]+/', '_', str_replace(':', '-', $matches[1]));

            $event->sendOutputTo(storage_path("logs/schedule-$slug.log"));
        }
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
