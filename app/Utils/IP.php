<?php

namespace App\Utils;

use Arr;
use Cache;
use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Http;
use Illuminate\Http\Client\PendingRequest;
use IP2Location\Database;
use ipip\db\City;
use Log;
use MaxMind\Db\Reader\InvalidDatabaseException;
use XdbSearcher;

use function request;

class IP
{
    public static function getClientIP(): ?string
    { // 获取访客真实IP
        return request()?->ip();
    }

    public static function getIPInfo(string $ip): array|false|null
    {// 获取IP地址信息
        $info = Cache::tags('IP_INFO')->get($ip);

        if (in_array($ip, ['::1', '127.0.0.1'], true)) {
            return false;
        }

        if ($info && ! empty(array_filter($info))) {
            return $info;
        }

        $ret = null;
        $source = 0;

        if (app()->getLocale() === 'zh_CN') {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) { // 中文ipv4
                while ($source <= 5 && ($ret === null || (is_array($ret) && empty(array_filter($ret))))) {
                    $ret = match ($source) {
                        0 => self::ipApi($ip),
                        1 => self::baiduBce($ip),
                        2 => self::TenAPI($ip),
                        3 => self::Baidu($ip),
                        4 => self::ipGeoLocation($ip),
                        5 => self::ip2Region($ip),
                    };
                    $source++;
                }
            } else {
                while ($source <= 9 && $ret === null) {  // 中文ipv6
                    $ret = match ($source) {
                        0 => self::baiduBce($ip),
                        1 => self::TenAPI($ip),
                        2 => self::TaoBao($ip),
                        3 => self::fkcoder($ip),
                        4 => self::ipApi($ip),
                        5 => self::juHe($ip),
                        6 => self::Baidu($ip),
                        7 => self::ipGeoLocation($ip),
                        8 => self::ip2Region($ip),
                        9 => self::IPIP($ip),
                        //10 => self::userAgentInfo($ip), // 无法查外网的ip
                    };
                    $source++;
                }
            }
        } else {
            while ($source <= 10 && ($ret === null || (is_array($ret) && empty(array_filter($ret))))) {  // 英文
                $ret = match ($source) {
                    0 => self::ipApi($ip),
                    1 => self::IPSB($ip),
                    2 => self::ipinfo($ip),
                    3 => self::ipGeoLocation($ip),
                    4 => self::dbIP($ip),
                    5 => self::IP2Online($ip),
                    6 => self::ipdata($ip),
                    7 => self::ipApiCo($ip),
                    8 => self::ip2Location($ip),
                    9 => self::GeoIP2($ip),
                    10 => self::ipApiCom($ip),
                };
                $source++;
            }
        }

        if ($ret !== null) {
            $ret['address'] = implode(' ', Arr::except(array_filter($ret), ['isp', 'latitude', 'longitude']));
            Cache::tags('IP_INFO')->put($ip, $ret, Day); // Store information for reduce API Calls
        }

        return $ret;
    }

    private static function ipApi(string $ip): ?array
    { // 开发依据: https://ip-api.com/docs/api:json
        $key = config('services.ip.ip-api_key');
        if ($key) {
            $response = self::setBasicHttp()->withHeaders(['Origin' => 'https://members.ip-api.com'])->acceptJson()->get("https://pro.ip-api.com/json/$ip?fields=49881&key=$key&lang=".str_replace('_', '-', app()->getLocale()));
            if (! $response->ok()) {
                $response = self::setBasicHttp()->acceptJson()->get("http://ip-api.com/json/$ip?fields=49881&lang=".str_replace('_', '-', app()->getLocale()));
            }
        } else {
            $response = self::setBasicHttp()->acceptJson()->get("http://ip-api.com/json/$ip?fields=49881&lang=".str_replace('_', '-', app()->getLocale()));
        }

        if ($response->ok()) {
            $data = $response->json();
            if ($data['status'] === 'success') {
                return [
                    'country' => $data['country'],
                    'region' => $data['regionName'],
                    'city' => $data['city'],
                    'isp' => $data['isp'],
                    'area' => null,
                    'latitude' => $data['lat'],
                    'longitude' => $data['lon'],
                ];
            }

            Log::error('【ip-api.com】ip查询失败：'.$data['message'] ?? '');
        } else {
            Log::error('【ip-api.com】查询无效：'.$ip);
        }

        return null;
    }

    private static function setBasicHttp(): PendingRequest
    {
        return Http::timeout(10)->withOptions(['http_errors' => false])->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');
    }

    private static function baiduBce(string $ip): ?array
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $url = "https://qifu-api.baidubce.com/ip/geo/v1/ipv6/district?ip=$ip";
        } else {
            $url = "https://qifu-api.baidubce.com/ip/geo/v1/district?ip=$ip";
        }
        $response = self::setBasicHttp()->get($url);
        $data = $response->json();
        if ($response->ok()) {
            if ($data['code'] === 'Success') {
                if (empty(array_filter($data['data']))) {
                    return null;
                }

                return [
                    'country' => $data['data']['country'],
                    'region' => $data['data']['prov'],
                    'city' => $data['data']['city'],
                    'isp' => $data['data']['isp'],
                    'area' => $data['data']['district'],
                    'latitude' => $data['data']['lat'],
                    'longitude' => $data['data']['lng'],
                ];
            }

            Log::error('【baiduBce】IP查询失败：'.$data['msg'] ?? '');
        } else {
            Log::error('【baiduBce】查询无效：'.$ip.var_export($data, true));
        }

        return null;
    }

    private static function TenAPI(string $ip): ?array
    { // 开发依据: https://docs.tenapi.cn/utility/getip.html
        $response = self::setBasicHttp()->asForm()->post('https://tenapi.cn/v2/getip', ['ip' => $ip]);
        if ($response->ok()) {
            $data = $response->json();

            if ($data['code'] === 200 && $data['data']['ip'] === $ip) {
                return [
                    'country' => $data['data']['country'],
                    'region' => $data['data']['province'],
                    'city' => $data['data']['city'],
                    'isp' => $data['data']['isp'],
                    'area' => '',
                ];
            }
        }

        return null;
    }

    private static function Baidu(string $ip): ?array
    {// 通过api.map.baidu.com查询IP地址的详细信息
        $key = config('services.ip.baidu_ak');
        if ($key) {
            // 依据 http://lbsyun.baidu.com/index.php?title=webapi/ip-api 开发
            $response = self::setBasicHttp()->get("https://api.map.baidu.com/location/ip?ak=$key&ip=$ip&coor=gcj02");

            if ($response->ok()) {
                $message = $response->json();
                if ($message['status'] === 0) {
                    $location = explode('|', $message['address']);

                    return [
                        'country' => $location[0],
                        'region' => $message['content']['address_detail']['province'],
                        'city' => $message['content']['address_detail']['city'],
                        'isp' => $location[4],
                        'area' => $message['content']['address_detail']['street'],
                        'latitude' => $message['content']['y'],
                        'longitude' => $message['content']['x'],
                    ];
                }

                Log::warning('【百度IP库】返回错误信息：'.$ip.PHP_EOL.var_export($message, true));
            } else {
                Log::error('【百度IP库】解析异常：'.$ip);
            }
        }

        return null;
    }

    private static function ipGeoLocation(string $ip): ?array
    { // 开发依据: https://ipgeolocation.io/documentation.html
        $response = self::setBasicHttp()->withHeaders(['Origin' => 'https://ipgeolocation.io'])
            ->get("https://api.ipgeolocation.io/ipgeo?ip=$ip&fields=country_name,state_prov,district,city,isp,latitude,longitude&lang=".config('common.language.'.app()->getLocale().'.1'));
        if ($response->ok()) {
            $data = $response->json();

            return [
                'country' => $data['country_name'],
                'region' => $data['state_prov'],
                'city' => $data['city'],
                'isp' => $data['isp'],
                'area' => $data['district'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ];
        }

        return null;
    }

    private static function ip2Region(string $ip): ?array
    { // 通过ip2Region查询IP地址的详细信息 数据库不经常更新
        try {
            $data = (new XdbSearcher())->search($ip);
        } catch (Exception $e) {
            Log::error('【ip2Region】错误信息：'.$e->getMessage());
        }

        if (! empty($data)) {
            $location = explode('|', $data);
            if ($location) {
                return [
                    'country' => $location[0],
                    'region' => $location[2],
                    'city' => $location[3],
                    'isp' => $location[4],
                    'area' => $location[1],
                ];
            }
        }

        return null;
    }

    private static function TaoBao(string $ip): ?array
    { // 通过ip.taobao.com查询IP地址的详细信息 依据 https://ip.taobao.com/instructions 开发
        $response = self::setBasicHttp()->post("https://ip.taobao.com/outGetIpInfo?ip=$ip&accessKey=alibaba-inc");

        if ($response->ok()) {
            $message = $response->json();
            if ($message['code'] === 0) {
                $data = $message['data'];

                return [
                    'country' => 'xx' !== strtolower($data['country']) ?: null,
                    'region' => 'xx' !== strtolower($data['region']) ?: null,
                    'city' => 'xx' !== strtolower($data['city']) ?: null,
                    'isp' => 'xx' !== strtolower($data['isp']) ?: null,
                    'area' => 'xx' !== strtolower($data['area']) ?: null,
                ];
            }

            Log::warning('【淘宝IP库】返回错误信息：'.$ip.PHP_EOL.$message['msg']);
        } else {
            Log::error('【淘宝IP库】解析异常：'.$ip);
        }

        return null;
    }

    private static function fkcoder(string $ip): ?array
    { // 开发依据: https://www.fkcoder.com/
        $response = self::setBasicHttp()->acceptJson()->get("https://www.fkcoder.com/ip?ip=$ip");
        if ($response->ok()) {
            $data = $response->json();

            return [
                'country' => $data['country'],
                'region' => $data['province'] ?: $data['region'],
                'city' => $data['city'],
                'isp' => $data['isp'],
                'area' => null,
            ];
        }

        return null;
    }

    private static function juHe(string $ip): ?array
    { // 开发依据: https://www.juhe.cn/docs/api/id/1
        $response = self::setBasicHttp()->asForm()->post('https://apis.juhe.cn/ip/Example/query.php', ['IP' => $ip]);
        if ($response->ok()) {
            $data = $response->json();
            if ($data['resultcode'] === '200' && $data['error_code'] === 0) {
                return [
                    'country' => $data['result']['Country'],
                    'region' => $data['result']['Province'],
                    'city' => $data['result']['City'],
                    'isp' => $data['result']['Isp'],
                    'area' => $data['result']['District'],
                ];
            }
        }

        return null;
    }

    private static function IPIP(string $ip): array
    { // 通过IPIP离线数据查询IP地址的详细信息
        $filePath = database_path('ipipfree.ipdb'); // 来源: https://www.ipip.net/free_download/
        $location = (new City($filePath))->findMap($ip, 'CN');

        return [
            'country' => $location['country_name'],
            'region' => $location['region_name'],
            'city' => $location['city_name'],
            'isp' => null,
            'area' => null,
        ];
    }

    private static function IPSB(string $ip): ?array
    { // 通过api.ip.sb查询IP地址的详细信息
        try {
            $response = self::setBasicHttp()->post("https://api.ip.sb/geoip/$ip");

            if ($response->ok()) {
                $data = $response->json();

                if ($data) {
                    $ret = Arr::only($data, ['country', 'region', 'city', 'isp', 'latitude', 'longitude']);

                    return Arr::prepend(['area' => null], $ret);
                }
            }

            Log::warning('[IPSB] 解析'.$ip.'异常: '.$response->body());
        } catch (Exception $e) {
            Log::error('[IPSB] 解析'.$ip.'错误: '.var_export($e->getMessage(), true));
        }

        return null;
    }

    private static function ipinfo(string $ip): ?array
    { // 开发依据: https://ipinfo.io/account/home
        $key = config('services.ip.ipinfo_token');
        if ($key) {
            $response = self::setBasicHttp()->acceptJson()->get("https://ipinfo.io/$ip?token=$key");
        } else {
            $response = self::setBasicHttp()->acceptJson()->withHeaders(['Referer' => 'https://ipinfo.io/'])->get("https://ipinfo.io/widget/demo/$ip");
        }

        if ($response->ok()) {
            $data = $key ? $response->json() : $response->json()['data'];

            $location = explode(',', $data['loc']);

            return [
                'country' => $data['country'],
                'region' => $data['region'],
                'city' => $data['city'],
                'isp' => $data['org'],
                'area' => null,
                'latitude' => $location[0],
                'longitude' => $location[1],
            ];
        }

        return null;
    }

    private static function dbIP(string $ip): ?array
    { // 开发依据: https://db-ip.com/api/doc.php
        $response = self::setBasicHttp()->acceptJson()->get("https://api.db-ip.com/v2/free/$ip");
        if ($response->ok()) {
            $data = $response->json();

            return [
                'country' => $data['countryName'],
                'region' => $data['stateProv'],
                'city' => $data['city'],
                'isp' => null,
                'area' => null,
            ];
        }

        return null;
    }

    private static function IP2Online(string $ip): ?array
    { // 开发依据: https://www.ip2location.io/ip2location-documentation
        $key = config('services.ip.IP2Location_key');
        if ($key) {
            $response = self::setBasicHttp()->acceptJson()->get("https://api.ip2location.io/?key=$key&ip=$ip");
            if ($response->ok()) {
                $data = $response->json();

                return [
                    'country' => $data['country_name'],
                    'region' => $data['region_name'],
                    'city' => $data['city_name'],
                    'isp' => $data['as'],
                    'area' => null,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ];
            }
        }

        return null;
    }

    private static function ipdata(string $ip): ?array
    { // 开发依据: https://docs.ipdata.co/docs
        $key = config('services.ip.ipdata_key');
        if ($key) {
            $response = self::setBasicHttp()->get("https://api.ipdata.co/$ip?api-key=$key&fields=ip,city,region,country_name,latitude,longitude,asn");
            if ($response->ok()) {
                $data = $response->json();

                return [
                    'country' => $data['country_name'],
                    'region' => $data['region'],
                    'city' => $data['city'],
                    'isp' => $data['asn']['name'],
                    'area' => null,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ];
            }
        }

        return null;
    }

    private static function ipApiCo(string $ip): ?array
    { // 开发依据: https://ipapi.co/api/
        $response = self::setBasicHttp()->get("https://ipapi.co/$ip/json/");
        if ($response->ok()) {
            $data = $response->json();

            return [
                'country' => $data['country_name'],
                'region' => $data['region'],
                'city' => $data['city'],
                'isp' => $data['org'],
                'area' => null,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ];
        }

        return null;
    }

    private static function ip2Location(string $ip): ?array
    { // 通过ip2Location查询IP地址的详细信息
        $filePath = database_path('IP2LOCATION-LITE-DB5.IPV6.BIN'); // 来源: https://lite.ip2location.com/database-download
        try {
            $location = (new Database($filePath, Database::FILE_IO))
                ->lookup($ip, [Database::CITY_NAME, Database::REGION_NAME, Database::COUNTRY_NAME, Database::LATITUDE, Database::LONGITUDE]);

            return [
                'country' => $location['countryName'],
                'region' => $location['regionName'],
                'city' => $location['cityName'],
                'isp' => null,
                'area' => null,
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ];
        } catch (Exception $e) {
            Log::error('【ip2Location】错误信息：'.$e->getMessage());
        }

        return null;
    }

    private static function GeoIP2(string $ip): ?array
    {// 通过GeoIP2查询IP地址的详细信息
        $filePath = database_path('GeoLite2-City.mmdb'); // 来源：https://github.com/PrxyHunter/GeoLite2/releases
        try {
            $location = (new Reader($filePath))->city($ip);

            return [
                'country' => $location->country->name,
                'region' => $location->mostSpecificSubdivision->name,
                'city' => $location->city->name,
                'isp' => null,
                'area' => null,
            ];
        } catch (AddressNotFoundException $e) {
            Log::error("【GeoIP2】查询失败：$ip ".$e->getMessage());
        } catch (InvalidDatabaseException $e) {
            Log::error("【GeoIP2】数据库无效：$ip ".$e->getMessage());
        }

        return null;
    }

    private static function ipApiCom(string $ip): ?array
    { // 开发依据: https://docs.ipdata.co/docs
        $response = self::setBasicHttp()->get("https://ipapi.com/ip_api.php?ip=$ip");
        if ($response->ok()) {
            $data = $response->json();

            return [
                'country' => $data['country_name'],
                'region' => $data['region_name'],
                'city' => $data['city'],
                'isp' => $data['connection']['isp'],
                'area' => null,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ];
        }

        return null;
    }

    public static function getIPGeo(string $ip): ?array
    {
        $ret = null;
        $source = 0;
        while ($source <= 10 && ($ret === null || (is_array($ret) && empty(array_filter($ret))))) {
            $ret = match ($source) {
                0 => self::IPSB($ip),
                1 => self::ipApi($ip),
                2 => self::baiduBce($ip),
                3 => self::ipinfo($ip),
                4 => self::IP2Online($ip),
                5 => self::Baidu($ip),
                6 => self::ipdata($ip),
                7 => self::ipGeoLocation($ip),
                8 => self::ipApiCo($ip),
                9 => self::ipApiCom($ip),
                10 => self::ip2Location($ip),
            };
            $source++;
        }

        return Arr::only($ret, ['latitude', 'longitude']);
    }

    private static function userAgentInfo(string $ip): ?array
    { // 开发依据: https://ip.useragentinfo.com/api
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $response = self::setBasicHttp()->get("https://ip.useragentinfo.com/ipv6/$ip");
        } else {
            $response = self::setBasicHttp()->withBody("ip:$ip")->get('https://ip.useragentinfo.com/json');
        }

        if ($response->ok()) {
            $data = $response->json();
            if ($data['code'] === 200 && $data['ip'] === $ip) {
                return [
                    'country' => $data['country'],
                    'region' => $data['province'],
                    'city' => $data['city'],
                    'isp' => $data['isp'],
                    'area' => $data['area'],
                ];
            }

            Log::error('【userAgentInfo】IP查询失败：'.$data ?? '');
        } else {
            Log::error('【userAgentInfo】查询无效：'.$ip);
        }

        return null;
    }
}
