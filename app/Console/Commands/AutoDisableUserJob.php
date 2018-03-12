<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\UserBanLog;
use App\Http\Models\User;
use Log;

class autoDisableUserJob extends Command
{
    protected $signature = 'autoDisableUserJob';
    protected $description = '自动禁用流量超限的用户';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 禁用流量超限账号
        $userList = User::query()->where('enable', 1)->whereRaw("u + d >= transfer_enable")->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                User::query()->where('id', $user->id)->update(['enable' => 0]);

                // 写入日志
                $this->log($user->id, '【自动封禁】-流量超限');
            }
        }

        Log::info('定时任务：' . $this->description);
    }

    private function log($user_id, $desc)
    {
        $log = new UserBanLog();
        $log->user_id = $user_id;
        $log->minutes = 0;
        $log->desc = $desc;
        $log->save();
    }
}
