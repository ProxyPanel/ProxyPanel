<?php

namespace App\Observers;

use App\Models\Config;
use Artisan;

class ConfigObserver
{
    public function updated(Config $config) // 更新设定
    {
        Artisan::call('optimize:clear');
        if (! config('app.debug')) {
            Artisan::call('optimize');
        }
    }
}
