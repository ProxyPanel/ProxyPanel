<?php

namespace App\Utils;

use App\Utils\Library\Templates\DNS;
use Cache;
use Log;
use ReflectionClass;

/**
 * Class DDNS 域名解析.
 */
class DDNS
{
    private DNS $dns;

    public function __construct(private readonly ?string $domain = null)
    {
        if ($domain) {
            foreach (glob(app_path('Utils/DDNS').'/*.php') as $file) {
                $class = 'App\\Utils\\DDNS\\'.basename($file, '.php');
                $reflectionClass = new ReflectionClass($class);

                if (sysConfig('ddns_mode') === $reflectionClass->getConstant('KEY')) {
                    $this->dns = new $class($domain);
                    break;
                }
            }
        }
    }

    public function destroy(string $type = '', string $ip = ''): void
    { // 删除解析记录
        if ($this->dns->destroy($type, $ip)) {
            Log::notice("【DDNS】删除：$this->domain 成功");
        } else {
            Log::alert("【DDNS】删除：$this->domain 失败，请手动删除！");
        }
    }

    public function update(string $latest_ip, string $original_ip, string $type = 'A'): void
    { // 修改解析记录
        if ($this->dns->update($latest_ip, $original_ip, $type)) {
            Log::info("【DDNS】更新 $this->domain ：$original_ip => $latest_ip 类型：$type 成功");
        } else {
            Log::warning("【DDNS】更新 $this->domain ：$original_ip => $latest_ip 类型：$type 失败，请手动设置！");
        }
    }

    public function store(string $ip, string $type = 'A'): void
    { // 添加解析记录
        if ($this->dns->store($ip, $type)) {
            Log::info("【DDNS】添加：$ip => $this->domain 类型：$type 成功");
        } else {
            Log::warning("【DDNS】添加：$ip => $this->domain 类型：$type 失败，请手动设置！");
        }
    }

    public function getLabels(): array
    {
        return Cache::rememberForever('ddns_get_Labels_'.app()->getLocale(), static function () {
            $labels[trans('common.status.closed')] = '';
            foreach (glob(app_path('Utils/DDNS').'/*.php') as $file) {
                $class = 'App\\Utils\\DDNS\\'.basename($file, '.php');
                $reflectionClass = new ReflectionClass($class);
                $labels[$reflectionClass->getConstant('LABEL')] = $reflectionClass->getConstant('KEY');
            }

            return $labels;
        });
    }
}
