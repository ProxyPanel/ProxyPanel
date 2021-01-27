<?php

namespace App\Observers;

use App\Models\Config;
use Cache;

class ConfigObserver
{
    public function updated(Config $config) // 更新设定
    {
        Cache::forget('settings');
    }
}
