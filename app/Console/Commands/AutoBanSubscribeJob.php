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
         * SSR、SSRR安卓客户端：
         *      okhttp/3.8.0
         *      Mozilla/5.0 (Linux; U; Android 4.4.4; zh-cn; MX4 Pro Build/KTU84P) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
         *
         * Shadowrocket：
         *      Shadowrocket/516 CFNetwork/893.14.2 Darwin/17.3.0
         *      Shadowrocket/510 CFNetwork/893.14.2 Darwin/17.3.0
         *      Shadowrocket/510 CFNetwork/889.9 Darwin/17.2.0
         *      Shadowrocket/510 CFNetwork/811.5.4 Darwin/16.7.0
         *      Shadowrocket/510 CFNetwork/811.5.4 Darwin/16.6.0
         *      Shadowrocket/510 CFNetwork/808.0.2 Darwin/16.0.0
         *      Shadowrocket/510 CFNetwork/758.4.3 Darwin/15.5.0
         *      Shadowrocket/510 CFNetwork/897.15 Darwin/17.5.0
         *      Shadowrocket/2.1.11 (iPhone; iOS 10.3.3; Scale/3.00)
         *      Shadowrocket/2.1.10 (iPhone; iOS 11.1.2; Scale/2.00)
         *      Shadowrocket/2.1.12 (iPhone; iOS 10.3.2; Scale/3.00)
         *      Mozilla/5.0 (iPhone; CPU iPhone OS 8_4 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12H143 Safari/600.1.4
         * ShadowsocksR win版：
         *      Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.3319.102 Safari/537.36
         *      Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36
         *      Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko
         * 梅林固件：
         *      curl/7.21.7 (arm-unknown-linux-gnu) libcurl/7.54.1 OpenSSL/1.0.2n zlib/1.2.5
         *      curl/7.21.7 (arm-unknown-linux-gnu) libcurl/7.54.1 OpenSSL/1.0.2l zlib/1.2.5
         *  curl/7.59.0
         * curl/7.60.0
         * curl/7.37.1
         * Mac SSR:
         *      ShadowsocksX-NG-R
         *      ShadowsocksX-NG-R 1.4.1-R8 Version 4
         *      ShadowsocksX-NG-R 1.4.3-R8 Version 2
         *      ShadowsocksX-NG-R 1.4.3-R8 Version 3
         *      Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36
         *      Mozilla/5.0 (Linux; Android 8.0.0; BLA-AL00 Build/HUAWEIBLA-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/62.0.3202.84 Mobile Safari/537.36 MicroMessenger/6.6.1.1220(0x26060135) NetType/4G Language/zh_CN
         * 带Cloudflare CDN：Cf-Connecting-Ip Cf-Ray Cf-Visitor
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
