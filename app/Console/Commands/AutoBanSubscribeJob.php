<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use Log;

class AutoBanSubscribeJob extends Command
{
    protected $signature = 'autoBanSubscribeJob';
    protected $description = '自动封禁异常订阅链接';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        /*
         * 客户端请求头有多种，常见如下：
         * SSR、SSRR安卓客户端：okhttp/3.8.0
         * Shadowrocket：Shadowrocket/516 CFNetwork/893.14.2 Darwin/17.3.0
         * ShadowsocksR win版：Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.3319.102 Safari/537.36
         */

        $config = $this->systemConfig();

        // 封禁24小时访问异常的订阅链接
        if ($config['is_subscribe_ban']) {
            $subscribeList = UserSubscribe::query()->where('status', 1)->get();
            if (!$subscribeList->isEmpty()) {
                foreach ($subscribeList as $subscribe) {
                    // 24小时内不同IP的请求次数
                    $request_times = UserSubscribeLog::query()->where('sid', $subscribe->id)->where('request_time', '>=', date("Y-m-d H:i:s", strtotime("-24 hours")))->distinct('request_ip')->count('request_ip');
                    if ($request_times >= $config['subscribe_ban_times']) {
                        UserSubscribe::query()->where('id', $subscribe->id)->update(['status' => 0, 'ban_time' => time(), 'ban_desc' => '存在异常，自动封禁']);
                    }
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }

    // 系统配置
    private function systemConfig() {
        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        return $data;
    }
}
