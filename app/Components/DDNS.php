<?php

namespace App\Components;

use App\Components\DDNS\Aliyun;
use App\Components\DDNS\Namesilo;
use Log;

/**
 * Class DDNS 域名解析
 *
 * @package App\Components
 */
class DDNS
{
    /**
     * 删除解析记录
     *
     * @param  string  $domain  域名
     * @param  string|null  $type
     * @return false|int
     */
    public static function destory($domain, $type = null)
    {
        return self::getClient($domain)->destroy($type);
    }

    public static function getClient($domain)
    {
        switch (sysConfig('ddns_mode')) {
            case 'aliyun':
                return (new Aliyun($domain));
            case 'namesilo':
                return new Namesilo($domain);
            default:
                Log::error("未知渠道：".sysConfig('ddns_mode'));

                return false;
        }
    }

    /**
     * 修改解析记录
     *
     * @param  string  $domain  域名
     * @param  string  $ip  ip地址
     * @param  string  $type  记录类型,默认为 A
     * @return void
     */
    public static function update($domain, $ip, $type = 'A')
    {
        return self::getClient($domain)->update($ip, $type);
    }

    /**
     * 添加解析记录
     *
     * @param  string  $domain  域名
     * @param  string  $ip  ip地址
     * @param  string  $type  记录类型,默认为 A
     * @return void
     */
    public static function store($domain, $ip, $type = 'A')
    {
        return self::getClient($domain)->store($ip, $type);
    }

}
