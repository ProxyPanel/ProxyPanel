<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\UserBanLog;
use App\Http\Models\User;
use App\Http\Models\UserTrafficHourly;
use Log;

class AutoBanUserJob extends Command
{
    protected $signature = 'autoBanUserJob';
    protected $description = '自动封禁用户';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $config = $this->systemConfig();

        // 封禁24小时内流量异常账号
        if ($config['is_traffic_ban']) {
            $userList = User::query()->where('status', '>=', 0)->where('enable', 1)->get();
            if (!$userList->isEmpty()) {
                foreach ($userList as $user) {
                    $time = date('Y-m-d H:i:s', time() - 24 * 60 * 60);
                    $totalTraffic = UserTrafficHourly::query()->where('user_id', $user->id)->where('node_id', 0)->where('created_at', '>=', $time)->sum('total');
                    if ($totalTraffic >= ($config['traffic_ban_value'] * 1024 * 1024 * 1024)) {
                        $ban_time = strtotime(date('Y-m-d H:i:s', strtotime("+" . $config['traffic_ban_time'] . " minutes")));
                        User::query()->where('id', $user->id)->update(['enable' => 0, 'ban_time' => $ban_time]);

                        // 写入日志
                        $this->log($user->id, $config['traffic_ban_time'], '【自动封禁】-24小时内流量异常');
                    }
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }

    // 系统配置
    private function systemConfig()
    {
        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        return $data;
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
