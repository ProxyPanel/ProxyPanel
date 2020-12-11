<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Mail\userTrafficWarning;
use App\Models\User;
use Illuminate\Console\Command;
use Log;
use Mail;

class UserTrafficAutoWarning extends Command
{
    protected $signature = 'userTrafficAutoWarning';
    protected $description = '用户流量超过警告阈值自动发邮件提醒';

    public function handle(): void
    {
        $jobStartTime = microtime(true);

        // 用户流量超过警告阈值自动发邮件提醒
        if (sysConfig('traffic_warning')) {
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
            if (false === filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $usedPercent = $user->used_traffic_percentage * 100; // 已使用流量百分比
            if ($usedPercent >= $trafficWarningPercent) {
                $logId = Helpers::addNotificationLog('流量提醒', '流量已使用：'.$usedPercent.'%，请保持关注。', 1, $user->email);
                Mail::to($user->email)->send(new userTrafficWarning($logId, $usedPercent));
            }
        }
    }
}
