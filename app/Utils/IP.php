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
    private static bool $is_ipv4;

    private static string $ip;

    private static PendingRequest $basicRequest;

    public static function getClientIP(): ?string
    { // 获取访客真实IP
        return request()?->ip();
    }

    public static function getIPInfo(string $ip): array|null|false
    {// 获取IP地址信息
        $info = Cache::tags('IP_INFO')->get($ip);

        if (in_array($ip, ['::1', '127.0.0.1'], true)) {
            return false;
        }

        if ($info && ! empty(array_filter($info))) {
            return $info;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            self::$is_ipv4 = true;
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            self::$is_ipv4 = false;
        } else {
            return false;
        }
        self::$ip = $ip;
        self::$basicRequest = Http::timeout(10)->withOptions(['http_errors' => false])->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');

        if (app()->getLocale() === 'zh_CN') {
            if (self::$is_ipv4) {
                $ret = self::IPLookup(['ipApi', 'Baidu', 'baiduBce', 'ipw', 'ipGeoLocation', 'TaoBao', 'speedtest', 'bjjii', 'TenAPI', 'fkcoder', 'vore', 'juHe', 'vvhan', 'ipjiance', 'ip2Region', 'IPDB']);
            } else {
                $ret = self::IPLookup(['ipApi', 'Baidu', 'baiduBce', 'ipw', 'ipGeoLocation', 'TenAPI', 'vore', 'ip2Region']);
            }
        } else {
            $ret = self::IPLookup(['ipApi', 'IPSB', 'ipinfo', 'ip234', 'ipGeoLocation', 'dbIP', 'IP2Online', 'ipdata', 'ipApiCo', 'ip2Location', 'GeoIP2', 'ipApiCom']);
        }

        if ($ret !== null) {
            $ret['address'] = implode(' ', Arr::except(array_filter($ret), ['isp', 'latitude', 'longitude']));
            Cache::tags('IP_INFO')->put($ip, $ret, Day); // Store information for reduce API Calls
        }

        return $ret;
    }

    private static function IPLookup(array $checkers): ?array
    {
        foreach ($checkers as $checker) {
            try {
                $result = self::callApi($checker);
                if (is_array($result) && ! empty(array_filter($result))) {
                    return $result;
                }
            } catch (Exception $e) {
                Log::error("[$checker] IP信息获取报错: ".$e->getMessage());

                continue;
            }
        }

        return null;
    }

    private static function callApi(string $checker): ?array
    {
        $ip = self::$ip;

        return match ($checker) {
            'ipApi' => self::ipApi($ip),
            'Baidu' => self::Baidu($ip),
            'baiduBce' => self::baiduBce($ip),
            'ipGeoLocation' => self::ipGeoLocation($ip),
            'TaoBao' => self::TaoBao($ip),
            'speedtest' => self::speedtest($ip),
            'TenAPI' => self::TenAPI($ip),
            'fkcoder' => self::fkcoder($ip),
            'juHe' => self::juHe($ip),
            'ip2Region' => self::ip2Region($ip),
            'IPDB' => self::IPDB($ip),
            'ipjiance' => self::ipjiance($ip),
            'IPSB' => self::IPSB($ip),
            'ipinfo' => self::ipinfo($ip),
            'ip234' => self::ip234($ip),
            'dbIP' => self::dbIP($ip),
            'IP2Online' => self::IP2Online($ip),
            'ipdata' => self::ipdata($ip),
            'ipApiCo' => self::ipApiCo($ip),
            'ip2Location' => self::ip2Location($ip),
            'GeoIP2' => self::GeoIP2($ip),
            'ipApiCom' => self::ipApiCom($ip),
            'vore' => self::vore($ip),
            'vvan' => self::vvhan($ip),
            'ipw' => self::ipw($ip),
            'bjjii' => self::bjjii($ip),
        };
    }

    private static function ipApi(string $ip): ?array
    { // 开发依据: https://ip-api.com/docs/api:json
        $key = config('services.ip.ip-api_key');
        if ($key) {
            $response = self::$basicRequest->withHeaders(['Origin' => 'https://members.ip-api.com'])->acceptJson()->get("https://pro.ip-api.com/json/$ip?fields=49881&key=$key&lang=".str_replace('_', '-', app()->getLocale()));
            if (! $response->ok()) {
                $response = self::$basicRequest->acceptJson()->get("http://ip-api.com/json/$ip?fields=49881&lang=".str_replace('_', '-', app()->getLocale()));
            }
        } else {
            $response = self::$basicRequest->acceptJson()->get("http://ip-api.com/json/$ip?fields=49881&lang=".str_replace('_', '-', app()->getLocale()));
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

    private static function Baidu(string $ip): ?array
    {// 通过api.map.baidu.com查询IP地址的详细信息
        $key = config('services.ip.baidu_ak');
        if ($key) {
            // 依据 http://lbsyun.baidu.com/index.php?title=webapi/ip-api 开发
            $response = self::$basicRequest->get("https://api.map.baidu.com/location/ip?ak=$key&ip=$ip&coor=gcj02");
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

    private static function baiduBce(string $ip): ?array
    {
        if (self::$is_ipv4) {
            $url = "https://qifu-api.baidubce.com/ip/geo/v1/district?ip=$ip";
        } else {
            $url = "https://qifu-api.baidubce.com/ip/geo/v1/ipv6/district?ip=$ip";
        }
        $response = self::$basicRequest->get($url);
        $data = $response->json();
        if ($response->ok()) {
            if ($data['code'] === 'Success' && $ip === $data['ip']) {
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

    private static function ipGeoLocation(string $ip): ?array
    { // 开发依据: https://ipgeolocation.io/documentation.html
        $response = self::$basicRequest->withHeaders(['Origin' => 'https://ipgeolocation.io'])
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

    private static function TaoBao(string $ip): ?array
    { // 通过ip.taobao.com查询IP地址的详细信息 依据 https://ip.taobao.com/instructions 开发
        $response = self::$basicRequest->retry(2)->post("https://ip.taobao.com/outGetIpInfo?ip=$ip&accessKey=alibaba-inc");

        $message = $response->json();
        if ($response->ok()) {
            $data = $message['data'];
            if ($message['code'] === 0 && $data['ip'] === $ip) {
                return [
                    'country' => strtolower($data['country']) !== 'xx' ?: null,
                    'region' => strtolower($data['region']) !== 'xx' ?: null,
                    'city' => strtolower($data['city']) !== 'xx' ?: null,
                    'isp' => strtolower($data['isp']) !== 'xx' ?: null,
                    'area' => strtolower($data['area']) !== 'xx' ?: null,
                ];
            }

            Log::warning('【淘宝IP库】返回错误信息：'.$ip.PHP_EOL.$message['msg']);
        } else {
            Log::error('【淘宝IP库】解析异常：'.$ip);
        }

        return null;
    }

    private static function speedtest(string $ip): ?array
    {
        $response = self::$basicRequest->get("https://forge.speedtest.cn/api/location/info?ip=$ip");
        $data = $response->json();
        if ($response->ok()) {
            if ($data['ip'] === $ip) {
                return [
                    'country' => $data['country'],
                    'region' => $data['province'],
                    'city' => $data['city'],
                    'isp' => $data['isp'],
                    'area' => $data['distinct'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lon'],
                ];
            }

            Log::error('【speedtest】IP查询失败');
        } else {
            Log::error('【speedtest】查询无效：'.$ip.var_export($data, true));
        }

        return null;
    }

    private static function TenAPI(string $ip): ?array
    { // 开发依据: https://docs.tenapi.cn/utility/getip.html
        $response = self::$basicRequest->asForm()->post('https://tenapi.cn/v2/getip', ['ip' => $ip]);
        if ($response->ok()) {
            $data = $response->json();

            if ($data['code'] === 200 && $data['data']['ip'] === $ip) {
                return [
                    'country' => $data['data']['country'],
                    'region' => $data['data']['province'],
                    'city' => $data['data']['city'],
                    'isp' => $data['data']['isp'],
                    'area' => null,
                ];
            }
        }

        return null;
    }

    private static function fkcoder(string $ip): ?array
    { // 开发依据: https://www.fkcoder.com/
        $response = self::$basicRequest->acceptJson()->get("https://www.fkcoder.com/ip?ip=$ip");
        if ($response->ok()) {
            $data = $response->json();

            return [
                'country' => $data['country'],
                'region' => $data['province'] ?: ($data['region'] ?: null),
                'city' => $data['city'],
                'isp' => $data['isp'],
                'area' => null,
            ];
        }

        return null;
    }

    private static function juHe(string $ip): ?array
    { // 开发依据: https://www.juhe.cn/docs/api/id/1
        $response = self::$basicRequest->asForm()->post('https://apis.juhe.cn/ip/Example/query.php', ['IP' => $ip]);
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

    private static function IPDB(string $ip): array
    { // 通过IPDB格式的离线数据查询IP地址的详细信息
        $filePath = database_path('qqwry.ipdb'); // 来源: https://github.com/metowolf/qqwry.ipdb
        $location = (new City($filePath))->findMap($ip, 'CN');

        return [
            'country' => $location['country_name'],
            'region' => $location['region_name'],
            'city' => $location['city_name'],
            'isp' => $location['isp_domain'],
            'area' => null,
        ];
    }

    private static function ipjiance(string $ip): ?array
    {
        $response = self::$basicRequest->get("https://www.ipjiance.com/api/geoip/report?ip=$ip");
        $data = $response->json();
        if ($response->ok()) {
            if ($data['code'] === 1) {
                return [
                    'country' => $data['data']['country'],
                    'region' => null,
                    'city' => $data['data']['city'],
                    'isp' => $data['data']['isp'],
                    'area' => null,
                    'latitude' => $data['data']['latitude'],
                    'longitude' => $data['data']['longitude'],
                ];
            }

            Log::error('【ipjiance】IP查询失败：'.$data['msg'] ?? '');
        } else {
            Log::error('【ipjiance】查询无效：'.$ip.var_export($data, true));
        }

        return null;
    }

    private static function IPSB(string $ip): ?array
    { // 通过api.ip.sb查询IP地址的详细信息
        try {
            $response = self::$basicRequest->post("https://api.ip.sb/geoip/$ip");

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
            $response = self::$basicRequest->acceptJson()->get("https://ipinfo.io/$ip?token=$key");
        } else {
            $response = self::$basicRequest->acceptJson()->withHeaders(['Referer' => 'https://ipinfo.io/'])->get("https://ipinfo.io/widget/demo/$ip");
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

    private static function ip234(string $ip): ?array
    {
        $response = self::$basicRequest->get("https://ip234.in/search_ip?ip=$ip");
        $data = $response->json();
        if ($response->ok()) {
            if ($data['code'] === 0) {
                return [
                    'country' => $data['data']['country'],
                    'region' => $data['data']['region'],
                    'city' => $data['data']['city'],
                    'isp' => $data['data']['organization'],
                    'area' => null,
                    'latitude' => $data['data']['latitude'],
                    'longitude' => $data['data']['longitude'],
                ];
            }

            Log::error('【ip234】IP查询失败：'.$data['msg'] ?? '');
        } else {
            Log::error('【ip234】查询无效：'.$ip.var_export($data, true));
        }

        return null;
    }

    private static function dbIP(string $ip): ?array
    { // 开发依据: https://db-ip.com/api/doc.php
        $response = self::$basicRequest->acceptJson()->get("https://api.db-ip.com/v2/free/$ip");
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
            $response = self::$basicRequest->acceptJson()->get("https://api.ip2location.io/?key=$key&ip=$ip");
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
            $response = self::$basicRequest->get("https://api.ipdata.co/$ip?api-key=$key&fields=ip,city,region,country_name,latitude,longitude,asn");
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
        $response = self::$basicRequest->get("https://ipapi.co/$ip/json/");
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
        $filePath = database_path('IP2LOCATION-LITE-DB11.IPV6.BIN'); // 来源: https://lite.ip2location.com/database-download
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
        $response = self::$basicRequest->get("https://ipapi.com/ip_api.php?ip=$ip");
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

    private static function vore(string $ip): ?array
    { // 开发依据: https://api.vore.top/
        $response = self::$basicRequest->get("https://api.vore.top/api/IPdata?ip=$ip");
        if ($response->ok()) {
            $data = $response->json();

            if ($data['code'] === 200) {
                return [
                    'country' => $data['ipdata']['info1'],
                    'region' => $data['ipdata']['info2'],
                    'city' => $data['ipdata']['info3'],
                    'isp' => $data['ipdata']['isp'],
                    'area' => null,
                ];
            }
        }

        return null;
    }

    private static function vvhan(string $ip): ?array
    {
        $response = self::$basicRequest->get("https://api.vvhan.com/api/getIpInfo?ip=$ip");
        if ($response->ok()) {
            $data = $response->json();

            if ($data['success'] && $data['ip'] === $ip) {
                return [
                    'country' => $data['info']['country'],
                    'region' => $data['info']['prov'],
                    'city' => $data['info']['city'],
                    'isp' => $data['info']['isp'],
                    'area' => null,
                ];
            }
        }

        return null;
    }

    private static function cz88(string $ip): ?array
    {
        $response = self::$basicRequest->get("https://www.cz88.net/api/cz88/ip/base?ip=$ip");
        if ($response->ok()) {
            $data = $response->json();

            if ($data['success'] && $data['data']['ip'] === $ip) {
                $data = $data['data'];
                $location = $data['locations'] ? $data['locations'][0] : null;

                return [
                    'country' => $data['country'],
                    'region' => $data['province'],
                    'city' => $data['city'],
                    'isp' => $data['isp'],
                    'area' => $data['districts'],
                    'latitude' => $location ? $location['latitude'] : null,
                    'longitude' => $location ? $location['longitude'] : null,
                ];
            }
        }

        return null;
    }

    private static function ipw(string $ip): ?array
    { // 开发依据: https://api.vore.top/
        if (self::$is_ipv4) {
            $response = self::$basicRequest->asForm()->withHeaders(['Referer' => 'https://ipw.cn/'])->post('https://rest.ipw.cn/api/ip/queryThird',
                ['ip' => $ip, 'param1' => '33546680dcec944422ee9fea64ced0fb6', 'param2' => '5ac8d31b5b3434350048af37a497a9']);
        } else {
            $response = self::$basicRequest->asForm()->withHeaders(['Referer' => 'https://ipw.cn/'])->get("https://rest.ipw.cn/api/aw/v1/ipv6?ip=$ip&warning=1");
        }

        if ($response->ok()) {
            $data = $response->json();
            if (self::$is_ipv4) {
                if ($data['result'] && $data['Result']['code'] === 'Success' && $data['Result']['ip'] === $ip) {
                    $data = $data['Result']['data'];

                    return [
                        'country' => $data['country'],
                        'region' => $data['prov'],
                        'city' => $data['city'],
                        'isp' => $data['isp'],
                        'area' => $data['district'],
                        'latitude' => $data['lat'],
                        'longitude' => $data['lng'],
                    ];
                }
            } elseif ($data['code'] === 'Success' && $data['ip'] === $ip) {
                $data = $data['data'];

                return [
                    'country' => $data['country'],
                    'region' => $data['prov'],
                    'city' => $data['city'],
                    'isp' => $data['isp'],
                    'area' => $data['district'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                ];
            }
        }

        return null;
    }

    private static function bjjii(string $ip): ?array
    { // 开发依据: https://api.bjjii.com/doc/77
        $key = config('services.ip.bjjii_key');
        if ($key) {
            $response = self::$basicRequest->get("https://api.bjjii.com/api/ip/query?key=$key&ip=$ip");
            if ($response->ok()) {
                $data = $response->json();

                if ($data['code'] === 200 && $data['data']['ip'] === $ip) {
                    $data = $data['data']['info'];

                    return [
                        'country' => $data['nation'],
                        'region' => $data['province'],
                        'city' => $data['city'],
                        'isp' => $data['isp'],
                        'area' => $data['district'],
                        'latitude' => $data['lat'],
                        'longitude' => $data['lng'],
                    ];
                }
            }
        }

        return null;
    }

    public static function getIPGeo(string $ip): array|false
    {
        self::$ip = $ip;
        self::$basicRequest = Http::timeout(10)->withOptions(['http_errors' => false])->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');

        $ret = self::IPLookup(['IPSB', 'ipApi', 'baiduBce', 'ipw', 'ipinfo', 'IP2Online', 'speedtest', 'bjjii', 'Baidu', 'ip234', 'ipdata', 'ipGeoLocation', 'ipjiance', 'ipApiCo', 'ipApiCom', 'ip2Location']);
        if (is_array($ret)) {
            return Arr::only($ret, ['latitude', 'longitude']);
        }

        return false;
    }

    private static function userAgentInfo(string $ip): ?array
    { // 开发依据: https://ip.useragentinfo.com/api 无法查外网的ip
        if (self::$is_ipv4) {
            $response = self::$basicRequest->withBody("ip:$ip")->get('https://ip.useragentinfo.com/json');
        } else {
            $response = self::$basicRequest->get("https://ip.useragentinfo.com/ipv6/$ip");
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
