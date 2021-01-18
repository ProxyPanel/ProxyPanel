<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AccountExpire;
use Illuminate\Console\Command;
use Log;

class UserExpireAutoWarning extends Command
{
    protected $signature = 'userExpireAutoWarning';
    protected $description = '用户临近到期自动发邮件提醒';

    public function handle(): void
    {
        $jobStartTime = microtime(true);

        // 用户临近到期自动发邮件提醒
        if (sysConfig('account_expire_notification')) {
            $this->userExpireWarning();
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    private function userExpireWarning(): void
    {
        // 只取SSR没被禁用的用户，其他不用管
        foreach (User::whereEnable(1)->where('expired_at', '<', date('Y-m-d', strtotime(sysConfig('expire_days').' days')))->get() as $user) {
            // 用户名不是邮箱的跳过
            if (filter_var($user->email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }
            $user->notify(new AccountExpire($user->expired_at));
        }
    }
}
