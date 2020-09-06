<?php

namespace App\Observers;

use App\Models\Config;
use Cache;

class ConfigObserver {
	public function updated(Config $config): void {
		// 更新系统参数缓存
		Cache::tags('sysConfig')->put($config->name, $config->value?: 0);
	}
}
