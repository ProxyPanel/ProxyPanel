<?php

namespace App\Helpers;

use App\Models\SsConfig;

trait ProxyConfig
{
    private function proxyConfigOptions(): array
    {
        // 一次性获取所有配置数据
        $configs = SsConfig::get(['name', 'type'])->groupBy('type')->map(fn ($items) => $items->pluck('name', 'name'));

        // 获取默认配置项
        $defaults = SsConfig::where('is_default', 1)->pluck('name', 'type');

        return [
            'methods' => $configs->get(1, []),
            'protocols' => $configs->get(2, []),
            'obfs' => $configs->get(3, []),
            'methodDefault' => $defaults->get(1),
            'protocolDefault' => $defaults->get(2),
            'obfsDefault' => $defaults->get(3),
        ];
    }
}
