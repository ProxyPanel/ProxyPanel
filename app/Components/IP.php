<?php

namespace App\Components;

use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use GuzzleHttp\Client;
use IP2Location\Database;
use Ip2Region;
use ipip\db\City;
use Log;
use MaxMind\Db\Reader\InvalidDatabaseException;

class IP
{
    // 获取IP地址信息
    public static function getIPInfo($ip)
    {
        // IPv6 推荐使用ip.sb
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            Log::info('识别到IPv6，尝试解析：'.$ip);
            $ipInfo = self::IPSB($ip);
        } else {
            $ipInfo = self::ip2Region($ip);
            if (!$ipInfo) {
                Log::info('无法识别，尝试使用【IPIP库】库解析：'.$ip);
                $ipInfo = self::ip2Location($ip);
            }
        }

        return $ipInfo;
    }

    // 通过api.ip.sb查询IP地址的详细信息
    public static function IPSB($ip)
    {
        $request = (new Client(['timeout' => 15]))->get('https://api.ip.sb/geoip/'.$ip);
        $message = json_decode($request->getBody(), true);

        if ($request->getStatusCode() === 200) {
            return $message;
        }

        Log::error('解析IPv6异常：'.$ip.PHP_EOL.var_export($request, true));

        return false;
    }

    // 通过ip2Region查询IP地址的详细信息 ← 聚合 淘宝IP库，GeoIP，纯真IP库
    public static function ip2Region(string $ip)
    {
        $ipInfo = false;
        try {
            $ipInfo = (new Ip2Region())->memorySearch($ip);
        } catch (Exception $e) {
            Log::error('【淘宝IP库】错误信息：'.$e->getMessage());
        }

        if ($ipInfo) {
            $location = explode("|", $ipInfo['region']);

            return [
                'country'  => $location[0] ?: '',
                'province' => $location[2] ?: '',
                'city'     => $location[3] ?: '',
            ];
        }

        return $ipInfo;
    }

    //// 通过ip2Location查询IP地址的详细信息
    public static function ip2Location(string $ip)
    {
        $filePath = database_path('IP2LOCATION-LITE-DB3.IPV6.BIN');
        try {
            $location = (new Database($filePath, Database::FILE_IO))
                ->lookup($ip, [Database::CITY_NAME, Database::REGION_NAME, Database::COUNTRY_NAME,]);

            return [
                'country'  => $location['countryName'],
                'province' => $location['regionName'],
                'city'     => $location['cityName'],
            ];
        } catch (Exception $e) {
            Log::error('【ip2Location】错误信息：'.$e->getMessage());
        }

        return false;
    }

    // 通过IPIP查询IP地址的详细信息
    public static function IPIP(string $ip): array
    {
        $filePath = database_path('ipip.ipdb');
        $location = (new City($filePath))->findMap($ip, 'CN');

        return [
            'country'  => $location['country_name'],
            'province' => $location['region_name'],
            'city'     => $location['city_name'],
        ];
    }

    // 通过ip.taobao.com查询IP地址的详细信息
    public static function TaoBao(string $ip)
    {
        // 依据 http://ip.taobao.com/instructions 开发
        $request = (new Client(['timeout' => 15]))->get('http://ip.taobao.com/outGetIpInfo?ip='.$ip.'&accessKey=alibaba-inc');
        $message = json_decode($request->getBody(), true);

        if ($request->getStatusCode() === 200) {
            if ($message['code'] === 0) {
                return [
                    'country'  => $message['data']['country'] === "XX" ? '' : $message['data']['country'],
                    'province' => $message['data']['region'] === "XX" ? '' : $message['data']['region'],
                    'city'     => $message['data']['city'] === "XX" ? '' : $message['data']['city'],
                ];
            }

            Log::error('【淘宝IP库】返回错误信息：'.$ip.PHP_EOL.var_export($message['msg'], true));
        } else {
            Log::error('【淘宝IP库】解析异常：'.$ip.PHP_EOL.var_export($request, true));
        }

        return false;
    }

    // 通过api.map.baidu.com查询IP地址的详细信息
    public static function Baidu(string $ip)
    {
        if (!env('BAIDU_APP_AK')) {
            Log::error('【百度IP库】AK信息缺失');

            return false;
        }
        // 依据 http://lbsyun.baidu.com/index.php?title=webapi/ip-api 开发
        $request = (new Client(['timeout' => 15]))->get('https://api.map.baidu.com/location/ip?ak='.env('BAIDU_APP_AK').'&'.$ip.'&coor=bd09ll');
        $message = json_decode($request->getBody(), true);

        if ($request->getStatusCode() === 200) {
            if ($message['status'] === 0) {
                return [
                    'country'  => $message['content']['address_detail']['country'],
                    'province' => $message['content']['address_detail']['province'],
                    'city'     => $message['content']['address_detail']['city'],
                ];
            }

            Log::error('【百度IP库】返回错误信息：'.$ip.PHP_EOL.var_export($message['message'], true));
        } else {
            Log::error('【百度IP库】解析异常：'.$ip.PHP_EOL.var_export($request, true));
        }

        return false;
    }

    // 通过GeoIP2查询IP地址的详细信息
    public static function GeoIP2(string $ip)
    {
        $filePath = database_path('maxmind.mmdb');
        try {
            $location = (new Reader($filePath))->city($ip);

            return [
                'country'  => $location->country->names['zh-CN'],
                'province' => '',
                'city'     => $location->city->name ?? '',
            ];
        } catch (AddressNotFoundException $e) {
            Log::error('【GeoIP2】查询失败：'.$ip);
        } catch (InvalidDatabaseException $e) {
            Log::error('【GeoIP2】数据库无效：'.$ip);
        }

        return false;
    }

    // 获取访客真实IP
    public static function getClientIP()
    {
        /*
         * 访问时用localhost访问的，读出来的是“::1”是正常情况
         * ::1说明开启了IPv6支持，这是IPv6下的本地回环地址的表示
         * 使用IPv4地址访问或者关闭IPv6支持都可以不显示这个
         */
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
                $ip = $_SERVER['REMOTE_ADDR'];
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            } else {
                $ip = 'unknown';
            }
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else {
            $ip = getenv('REMOTE_ADDR');
        }

        if (trim($ip) === '::1') {
            $ip = '127.0.0.1';
        }

        return $ip;
    }
}
