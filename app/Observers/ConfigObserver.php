<?php

namespace App\Observers;

use App\Components\Helpers;
use App\Models\Config;
use Cache;
use Illuminate\Support\Arr;

class ConfigObserver
{
    public function updated(Config $config): void
    {
        // 更新系统参数缓存
        Cache::tags('sysConfig')->put($config->name, $config->value ?? 0);

        // 如果在线支付方式出现变动，改变 在线支付 设置状态
        if (Arr::exists(['is_AliPay', 'is_QQPay', 'is_WeChatPay', 'is_otherPay'], $config->name) && Cache::tags('sysConfig')->has('is_onlinePay')) {
            Helpers::cacheSysConfig('is_onlinePay');
        }
    }
}
