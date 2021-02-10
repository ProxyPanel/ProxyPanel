<?php

namespace App\Observers;

use App\Models\Config;
use Artisan;

class ConfigObserver
{
    public function updated(Config $config) // 更新设定
    {
        if (config('app.debug')) {
            Artisan::call('optimize:clear');
        } else {
            Artisan::call('optimize:clear');
            Artisan::call('optimize');
        }
    }
}
