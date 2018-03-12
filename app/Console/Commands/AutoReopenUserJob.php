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
