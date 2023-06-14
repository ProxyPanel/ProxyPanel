<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DataExhaust;
use Illuminate\Console\Command;
use Log;

class UserTrafficWarning extends Command
{
    protected $signature = 'userTrafficWarning';

    protected $description = '用户流量超过警告阈值自动发邮件提醒';

    public function handle(): void
    {
        $jobTime = microtime(true);

        if (sysConfig('data_exhaust_notification')) { // 用户流量超过警告阈值提醒
            $this->userTrafficWarning();
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    private function userTrafficWarning(): void
    { // 用户流量超过警告阈值提醒
        $trafficWarningPercent = sysConfig('traffic_warning_percent');
        User::activeUser()->where('transfer_enable', '>', 0)->chunk(config('tasks.chunk'), function ($users) use ($trafficWarningPercent) {
            foreach ($users as $user) {
                // 用户账号不是邮箱的跳过
                if (filter_var($user->username, FILTER_VALIDATE_EMAIL) === false) {
                    continue;
                }

                $usedPercent = $user->used_traffic_percentage * 100; // 已使用流量百分比
                if ($usedPercent >= $trafficWarningPercent) {
                    $user->notify(new DataExhaust($usedPercent));
                }
            }
        });
    }
}
