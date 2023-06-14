<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AccountExpire;
use Illuminate\Console\Command;
use Log;

class UserExpireWarning extends Command
{
    protected $signature = 'userExpireWarning';

    protected $description = '用户临近到期自动提醒';

    public function handle(): void
    {
        $jobTime = microtime(true);

        if (sysConfig('account_expire_notification')) {// 用户临近到期自动提醒
            $this->userExpireWarning();
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    private function userExpireWarning(): void
    {
        // 只取没被禁用的用户，其他不用管
        User::whereEnable(1)
            ->where('expired_at', '<', date('Y-m-d', strtotime(sysConfig('expire_days').' days')))
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    if (filter_var($user->username, FILTER_VALIDATE_EMAIL) === false) { // 用户账号不是邮箱的跳过
                        continue;
                    }
                    $user->notify(new AccountExpire($user->expired_at->diffInDays()));
                }
            });
    }
}
