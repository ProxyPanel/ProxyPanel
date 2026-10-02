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
        $today = today()->toDateString();
        $deadline = today()->addDays((int) sysConfig('expire_days'))->toDateString();

        // 只取没被禁用的用户，其他不用管
        // 到期时间必须带下界：只写“早于 今天+N 天”会把早已过期的用户也算进来，命中集合只增不减，每日提醒量会持续膨胀
        User::whereEnable(1)
            ->whereBetween('expired_at', [$today, $deadline])
            ->where(static function ($query) use ($today) { // 同一天已提醒过的跳过，保证重复执行不会重复推送
                $query->whereNull('expire_warned_at')->orWhere('expire_warned_at', '<', $today);
            })
            ->chunkById((int) sysConfig('tasks_chunk', 3000), static function ($users) use ($today) {
                foreach ($users as $user) {
                    if (filter_var($user->username, FILTER_VALIDATE_EMAIL) === false) { // 用户账号不是邮箱的跳过
                        continue;
                    }

                    $user->notify(new AccountExpire($user->expired_at->diffInDays()));

                    // 标记已提醒（静默写入，避免触发 UserObserver 与多余的 updated_at 变更）
                    $user->updateQuietly(['expire_warned_at' => $today]);
                }
            });
    }
}
