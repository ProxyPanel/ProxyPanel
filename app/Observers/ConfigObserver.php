<?php

namespace App\Observers;

use App\Models\Config;
use Arr;
use Cache;

class ConfigObserver
{

    public function updated(Config $config): void
    {
        // 更新系统参数缓存
        Cache::tags('sysConfig')->put($config->name, $config->value ?: 0);

        // 如果在线支付方式出现变动，改变 在线支付 设置状态
        if (Arr::exists(['is_AliPay', 'is_QQPay', 'is_WeChatPay', 'is_otherPay'], $config->name)) {
            $value = !empty(array_filter(Cache::many(['is_AliPay', 'is_QQPay', 'is_WeChatPay', 'is_otherPay'])));
            Cache::tags('sysConfig')->put('is_onlinePay', $value);
        }
    }

}
