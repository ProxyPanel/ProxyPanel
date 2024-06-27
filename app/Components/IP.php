<?php

namespace App\Components;

use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Http;
use IP2Location\Database;
use Ip2Region;
use ipip\db\City;
use Log;
use MaxMind\Db\Reader\InvalidDatabaseException;

class IP
{
    public static function getIPInfo($ip) // 获取IP地址信息
    {
        // IPv6 推荐使用ip.sb
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            Log::notice('识别到IPv6，尝试解析：'.$ip);
            $ipInfo = self::IPSB($ip);
        } else {
            $ipInfo = self::ip2Region($ip);
            if (! $ipInfo) {
                Log::warning('无法识别，尝试使用【IPIP库】库解析：'.$ip);
                $ipInfo = self::ip2Location($ip);
            }
        }

        return $ipInfo;
    }

    public static function IPSB($ip) // 通过api.ip.sb查询IP地址的详细信息
    {
        try {
            $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36'])->timeout(15)->post('https://api.ip.sb/geoip/'.$ip);

            if ($response->ok()) {
                return $response->json();
            }

            Log::warning('[IPSB] 解析'.$ip.'异常: '.$response->body());

            return false;
        } catch (Exception $e) {
            Log::error('[IPSB] 解析'.$ip.'错误: '.var_export($e->getMessage(), true));

            return false;
        }
    }

    public static function ip2Region(string $ip) // 通过ip2Region查询IP地址的详细信息 ← 聚合 淘宝IP库，GeoIP，纯真IP库
    {
        $ipInfo = false;
        try {
            $ipInfo = (new Ip2Region())->memorySearch($ip);
        } catch (Exception $e) {
            Log::error('【ip2Region】错误信息：'.$e->getMessage());
        }

        if ($ipInfo) {
            $location = explode('|', $ipInfo['region']);
            if ($location) {
                return [
                    'country'  => $location[0] ?: '',
                    'province' => $location[2] ?: '',
                    'city'     => $location[3] ?: '',
                    'isp'      => $location[4] ?: '',
                    'area'     => $location[1] ?: '',
                ];
            }
        }

        return $ipInfo;
    }

    public static function ip2Location(string $ip) // 通过ip2Location查询IP地址的详细信息
    {
        $filePath = database_path('IP2LOCATION-LITE-DB3.IPV6.BIN'); // 来源: https://lite.ip2location.com/database-download IP-COUNTRY-REGION-CITY的BIN
        try {
            $location = (new Database($filePath, Database::FILE_IO))
                ->lookup($ip, [Database::CITY_NAME, Database::REGION_NAME, Database::COUNTRY_NAME]);

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

    public static function IPIP(string $ip): array // 通过IPIP离线数据查询IP地址的详细信息
    {
        $filePath = database_path('ipipfree.ipdb'); // 来源: https://www.ipip.net/free_download/
        $location = (new City($filePath))->findMap($ip, 'CN');

        return [
            'country'  => $location['country_name'],
            'province' => $location['region_name'],
            'city'     => $location['city_name'],
        ];
    }

    public static function IPIPOnline(string $ip) // 通过IPIP在线查询IP地址的详细信息
    { // https://freeapi.ipip.net
        $response = Http::timeout(15)->get('https://freeapi.ipip.net/'.$ip);

        if ($response->ok()) {
            $message = $response->json();
            if ($message) {
                return [
                    'country'  => $message[0],
                    'province' => $message[1],
                    'city'     => $message[2],
                ];
            }

            Log::warning('【IPIP在线】返回错误信息：'.$ip.PHP_EOL.$message['msg']);
        } else {
            Log::error('【IPIP在线】解析异常：'.$ip);
        }

        return false;
    }

    public static function TaoBao(string $ip) // 通过ip.taobao.com查询IP地址的详细信息
    {
        // 依据 https://ip.taobao.com/instructions 开发
        $response = Http::timeout(15)->post('https://ip.taobao.com/outGetIpInfo?ip='.$ip.'&accessKey=alibaba-inc');

        if ($response->ok()) {
            $message = $response->json();
            if ($message['code'] === 0) {
                return [
                    'country'  => $message['data']['country'] === 'XX' ? '' : $message['data']['country'],
                    'province' => $message['data']['region'] === 'XX' ? '' : $message['data']['region'],
                    'city'     => $message['data']['city'] === 'XX' ? '' : $message['data']['city'],
                    'isp'      => $message['data']['isp'] === 'XX' ? '' : $message['data']['isp'],
                ];
            }

            Log::warning('【淘宝IP库】返回错误信息：'.$ip.PHP_EOL.$message['msg']);
        } else {
            Log::error('【淘宝IP库】解析异常：'.$ip);
        }

        return false;
    }

    public static function Baidu(string $ip) // 通过api.map.baidu.com查询IP地址的详细信息
    {
        if (! config('services.baidu.app_ak')) {
            Log::error('【百度IP库】AK信息缺失');

            return false;
        }
        // 依据 http://lbsyun.baidu.com/index.php?title=webapi/ip-api 开发
        $response = Http::timeout(15)->get('https://api.map.baidu.com/location/ip?ak='.config('services.baidu.app_ak').'&'.$ip.'&coor=bd09ll');

        if ($response->ok()) {
            $message = $response->json();
            if ($message['status'] === 0) {
                return [
                    'country'  => $message['content']['address_detail']['country'],
                    'province' => $message['content']['address_detail']['province'],
                    'city'     => $message['content']['address_detail']['city'],
                    'area'     => $message['address'],
                ];
            }

            Log::warning('【百度IP库】返回错误信息：'.$ip.PHP_EOL.var_export($message['message'], true));
        } else {
            Log::error('【百度IP库】解析异常：'.$ip);
        }

        return false;
    }

    public static function GeoIP2(string $ip) // 通过GeoIP2查询IP地址的详细信息
    {
        $filePath = database_path('GeoLite2-City.mmdb'); // 来源：https://github.com/PrxyHunter/GeoLite2/releases
        try {
            $location = (new Reader($filePath))->city($ip);

            return [
                'country'  => $location->country->name ?? '',
                'province' => $location->mostSpecificSubdivision->name ?? '',
                'city'     => $location->city->name ?? '',
            ];
        } catch (AddressNotFoundException $e) {
            Log::error('【GeoIP2】查询失败：'.$ip);
        } catch (InvalidDatabaseException $e) {
            Log::error('【GeoIP2】数据库无效：'.$ip);
        }

        return false;
    }

    public static function getClientIP() // 获取访客真实IP
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
