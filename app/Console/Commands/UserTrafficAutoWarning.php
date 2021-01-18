<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DataExhaust;
use Illuminate\Console\Command;
use Log;

class UserTrafficAutoWarning extends Command
{
    protected $signature = 'userTrafficAutoWarning';
    protected $description = '用户流量超过警告阈值自动发邮件提醒';

    public function handle(): void
    {
        $jobStartTime = microtime(true);

        // 用户流量超过警告阈值自动发邮件提醒
        if (sysConfig('data_exhaust_notification')) {
            $this->userTrafficWarning();
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    // 用户流量超过警告阈值自动发邮件提醒
    private function userTrafficWarning(): void
    {
        $trafficWarningPercent = sysConfig('traffic_warning_percent');
        foreach (User::activeUser()->where('transfer_enable', '>', 0)->get() as $user) {
            // 用户名不是邮箱的跳过
            if (filter_var($user->email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            $usedPercent = $user->used_traffic_percentage * 100; // 已使用流量百分比
            if ($usedPercent >= $trafficWarningPercent) {
                $user->notify(new DataExhaust($usedPercent));
            }
        }
    }
}
