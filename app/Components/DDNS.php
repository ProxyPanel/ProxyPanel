<?php

namespace App\Components;

use App\Components\DDNS\Aliyun;
use App\Components\DDNS\CloudFlare;
use App\Components\DDNS\DNSPod;
use App\Components\DDNS\Namesilo;
use Log;

/**
 * Class DDNS 域名解析.
 */
class DDNS
{
    /**
     * 删除解析记录.
     *
     * @param  string  $domain  域名
     * @param  string|null  $type
     */
    public static function destroy(string $domain, $type = null)
    {
        if (self::dnsProvider($domain)->destroy($type)) {
            Log::notice("【DDNS】删除：{$domain} 成功");
        } else {
            Log::alert("【DDNS】删除：{$domain} 失败，请手动删除！");
        }
    }

    private static function dnsProvider($domain)
    {
        switch (sysConfig('ddns_mode')) {
            case 'aliyun':
                return new Aliyun($domain);
            case 'namesilo':
                return new Namesilo($domain);
            case 'dnspod':
                return new DNSPod($domain);
            case 'cloudflare':
                return new CloudFlare($domain);
            default:
                Log::emergency('【DDNS】未知渠道：'.sysConfig('ddns_mode'));

                return false;
        }
    }

    /**
     * 修改解析记录.
     *
     * @param  string  $domain  域名
     * @param  string  $ip  ip地址
     * @param  string  $type  记录类型,默认为 A
     */
    public static function update(string $domain, string $ip, string $type = 'A')
    {
        if (self::dnsProvider($domain)->update($ip, $type)) {
            Log::info("【DDNS】更新：{$ip} => {$domain} 类型：{$type} 成功");
        } else {
            Log::warning("【DDNS】更新：{$ip} => {$domain} 类型：{$type} 失败，请手动设置！");
        }
    }

    /**
     * 添加解析记录.
     *
     * @param  string  $domain  域名
     * @param  string  $ip  ip地址
     * @param  string  $type  记录类型,默认为 A
     */
    public static function store(string $domain, string $ip, string $type = 'A')
    {
        if (self::dnsProvider($domain)->store($ip, $type)) {
            Log::info("【DDNS】添加：{$ip} => {$domain} 类型：{$type} 成功");
        } else {
            Log::warning("【DDNS】添加：{$ip} => {$domain} 类型：{$type} 失败，请手动设置！");
        }
    }
}
