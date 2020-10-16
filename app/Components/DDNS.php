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
     * @return false|int
     */
    public static function destroy(string $domain, $type = null)
    {
        return self::dnsProvider($domain)->destroy($type);
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
                Log::error('未知渠道：'.sysConfig('ddns_mode'));

                return false;
        }
    }

    /**
     * 修改解析记录.
     *
     * @param  string  $domain  域名
     * @param  string  $ip  ip地址
     * @param  string  $type  记录类型,默认为 A
     * @return array|false|mixed
     */
    public static function update(string $domain, string $ip, string $type = 'A')
    {
        return self::dnsProvider($domain)->update($ip, $type);
    }

    /**
     * 添加解析记录.
     *
     * @param  string  $domain  域名
     * @param  string  $ip  ip地址
     * @param  string  $type  记录类型,默认为 A
     * @return array|false|mixed
     */
    public static function store(string $domain, string $ip, string $type = 'A')
    {
        return self::dnsProvider($domain)->store($ip, $type);
    }
}
