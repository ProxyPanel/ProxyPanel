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
        // 域名出现变动，更新路由设定
        if (in_array($config->name, ['subscribe_domain', 'web_api_url', 'website_callback_url'])) {
            if (config('app.debug')) {
                Artisan::call('optimize:clear');
            } else {
                Artisan::call('optimize');
            }
        }
    }
}
