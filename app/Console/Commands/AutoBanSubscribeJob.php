<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use Log;

class AutoBanSubscribeJob extends Command
{
    protected $signature = 'command:autoBanSubscribeJob';
    protected $description = '自动封禁异常订阅链接';

    protected static $config;

    public function __construct()
    {
        parent::__construct();

        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        self::$config = $data;
    }

    public function handle()
    {
        // 封禁24小时访问异常的订阅链接
        if (self::$config['is_subscribe_ban']) {
            $subscribeList = UserSubscribe::query()->where('status', 1)->get();
            if (!$subscribeList->isEmpty()) {
                foreach ($subscribeList as $subscribe) {
                    // 24小时内的请求次数
                    $request_times = UserSubscribeLog::query()->where('sid', $subscribe->id)->where('request_time', '>=', date("Y-m-d H:i:s", strtotime("-24 hours")))->distinct('request_ip')->count('request_ip');
                    if ($request_times >= self::$config['subscribe_ban_times']) {
                        UserSubscribe::query()->where('id', $subscribe->id)->update(['status' => 0, 'ban_time' => time(), 'ban_desc' => '存在异常，自动封禁']);
                    }
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
