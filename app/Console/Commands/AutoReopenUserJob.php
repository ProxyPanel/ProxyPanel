<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\UserBanLog;
use App\Http\Models\User;
use Log;

class AutoReopenUserJob extends Command
{
    protected $signature = 'autoReopenUserJob';
    protected $description = '自动解封用户';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 解封账号
        $userList = User::query()->where('status', '>=', 0)->where('ban_time', '>', 0)->get();
        foreach ($userList as $user) {
            if ($user->ban_time < time()) {
                User::query()->where('id', $user->id)->update(['enable' => 1, 'ban_time' => 0]);

                // 写入操作日志
                $this->log($user->id, 0, '【自动解封】-封禁到期');
            }
        }

        // SSR(R)被启用说明用户购买了流量
        User::query()->where('enable', 1)->where('ban_time', -1)->update(['ban_time' => 0]); // 重置ban_time
        $userList = User::query()->where('status', '>=', 0)->where('enable', 0)->where('ban_time', -1)->whereRaw("u + d < transfer_enable")->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                User::query()->where('id', $user->id)->update(['enable' => 1, 'ban_time' => 0]);

                // 写入操作日志
                $this->log($user->id, 0, '【自动解封】-有流量解封');
            }
        }

        Log::info('定时任务：' . $this->description);
    }


    private function log($user_id, $minutes, $desc)
    {
        $log = new UserBanLog();
        $log->user_id = $user_id;
        $log->minutes = $minutes;
        $log->desc = $desc;
        $log->save();
    }
}
