<?php

namespace App\Utils;

use App\Utils\DDNS\AliYun;
use App\Utils\DDNS\CloudFlare;
use App\Utils\DDNS\DNSPod;
use App\Utils\DDNS\Namesilo;
use App\Utils\Library\Templates\DNS;
use Log;

/**
 * Class DDNS 域名解析.
 */
class DDNS
{
    private DNS $dns;

    public function __construct(private readonly string $domain)
    {
        $this->dns = match (sysConfig('ddns_mode')) {
            'aliyun' => new AliYun($domain),
            'namesilo' => new Namesilo($domain),
            'dnspod' => new DNSPod($domain),
            'cloudflare' => new CloudFlare($domain),
        };
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
}
