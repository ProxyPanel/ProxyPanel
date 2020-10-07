<?php

namespace App\Observers;

use App\Components\Helpers;
use App\Models\Config;
use Artisan;
use Cache;

class ConfigObserver
{
    public function updated(Config $config): void
    {
        // 更新系统参数缓存
        Cache::tags('sysConfig')->put($config->name, $config->value ?? 0);

        // 如果在线支付方式出现变动，改变 在线支付 设置状态
        if (in_array($config->name, ['is_AliPay', 'is_QQPay', 'is_WeChatPay', 'is_otherPay']) && Cache::tags('sysConfig')->has('is_onlinePay')) {
            Helpers::cacheSysConfig('is_onlinePay');
        }

        // 域名出现变动，更新路由设定
        if (in_array($config->name, ['subscribe_domain', 'web_api_url', 'website_callback_url'])) {
            if (config('app.debug')) {
                Artisan::call('route:clear');
            } else {
                Artisan::call('route:cache');
            }
        }
    }
}
