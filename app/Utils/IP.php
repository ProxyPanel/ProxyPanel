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

class IP
{
    private const CACHE_TAG = 'IP_INFO'; // 公共常量 / 默认键

    private static ?PendingRequest $basicRequest = null;

    public static function getClientIP(): ?string
    { // 获取访客真实IP
        return request()?->ip();
    }

    public static function getIPInfo(string $ip, ?string $checker = null): array|null|false
    { // 获取 IP 信息
        if (in_array($ip, ['::1', '127.0.0.1'], true)) {
            return false;
        }

        if ($checker !== null) {
            $result = self::IPLookup($ip, [$checker]);
        } else {
            $cached = Cache::tags(self::CACHE_TAG)->get($ip);
            if ($cached && ! empty(array_filter($cached))) {
                return $cached;
            }

            $isIpv4 = self::isIpv4($ip);
            if (app()->getLocale() === 'zh_CN') {
                $checkers = $isIpv4
                    ? ['ipApi', 'Baidu', 'baiduBce', 'ipw', 'ipGeoLocation', 'TaoBao', 'speedtest', 'bjjii', 'vore', 'juHe', 'ip2Region', 'IPDB', 'ipwhois', 'pconline']
                    : ['ipApi', 'Baidu', 'baiduBce', 'ipw', 'ipGeoLocation', 'vore', 'ip2Region'];
            } else {
                $checkers = ['ipApi', 'IPSB', 'ipinfo', 'ip234', 'ipGeoLocation', 'dbIP', 'IP2Online', 'ipdata', 'ipApiIS', 'ipApiCo', 'ip2Location', 'GeoIP2', 'ipApiCom', 'ipApiIO', 'freeipapi'];
            }

            $result = self::IPLookup($ip, $checkers);
        }

        if ($result !== null) {
            $result['address'] = implode(' ', Arr::except(array_filter($result), ['isp', 'latitude', 'longitude']));
            Cache::tags(self::CACHE_TAG)->put($ip, $result, Day);
        }

        return $result;
    }

    private static function IPLookup(string $ip, array $checkers): ?array
    {
        foreach ($checkers as $checker) {
            if (! method_exists(self::class, $checker)) {
                continue;
            }

            try {
                $result = call_user_func([self::class, $checker], $ip);
                if (is_array($result) && ! empty(array_filter($result))) {
                    return $result;
                }
            } catch (Exception $e) {
                Log::error("[$checker] IP信息获取报错: ".$e->getMessage());
            }
        }

        return null;
    }

    private static function isIpv4(string $ip): bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    public static function getIPGeo(string $ip, ?string $checker = null): array|false
    { // 仅获取经纬度
        if ($checker !== null) {
            $ret = self::IPLookup($ip, [$checker]);
        } else {
            $ret = self::IPLookup($ip, ['IPSB', 'ipApi', 'ipw', 'ipinfo', 'IP2Online', 'speedtest', 'bjjii', 'Baidu', 'ip234', 'ipdata', 'ipGeoLocation', 'ipApiIS', 'ipApiCo', 'ipApiCom', 'ip2Location', 'ipApiIO', 'ipwhois', 'freeipapi']);
        }

        if (is_array($ret)) {
            return Arr::only($ret, ['latitude', 'longitude']);
        }

        return false;
    }

    private static function http(): PendingRequest
    { // 统一的HTTP客户端方法
        if (! self::$basicRequest) {
            self::$basicRequest = Http::timeout(5)->retry(2)->withOptions(['http_errors' => false])->withoutVerifying()->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36');
        }

        return self::$basicRequest;
    }

    private static function ipApi(string $ip): ?array
    { // 开发依据: https://ip-api.com/docs/api:json
        $client = self::http()->withHeader('Origin', 'https://members.ip-api.com');
        $lang = str_replace('_', '-', app()->getLocale());
        $key = config('services.ip.ip-api_key');

        if (empty($key)) {
            $response = $client->get("https://demo.ip-api.com/json/$ip?fields=582361&key=$key&lang=$lang");
        } else {
            $response = $client->get("https://pro.ip-api.com/json/$ip?fields=582361&key=$key&lang=$lang");
        }

        if ($response->ok()) {
            $data = $response->json();
            if ($data['status'] === 'success' && $data['query'] === $ip) {
                return [
                    'country' => $data['country'] ?? null,
                    'region' => $data['regionName'] ?? null,
                    'city' => $data['city'] ?? null,
                    'isp' => $data['isp'] ?? null,
                    'area' => $data['district'] ?? null,
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null,
                ];
            }
            Log::error('【ip-api.com】ip查询失败：'.($data['message'] ?? 'unknown'));
        } else {
            Log::error('【ip-api.com】查询无效：'.$ip);
        }

        return null;
    }

    private static function Baidu(string $ip): ?array
    { // 通过api.map.baidu.com查询IP地址的详细信息，依据 http://lbsyun.baidu.com/index.php?title=webapi/ip-api 开发
        $client = self::http();
        $key = config('services.ip.baidu_ak');
        if (empty($key)) {
            return null;
        }

        $response = $client->get("https://api.map.baidu.com/location/ip?ak=$key&ip=$ip&coor=gcj02");
        if (! $response->ok()) {
            Log::error('【百度IP库】解析异常：'.$ip);

            return null;
        }

        $message = $response->json();
        if ($message['status'] === 0) {
            $location = isset($message['address']) ? explode('|', $message['address']) : [];

            return [
                'country' => $location[0] ?? null,
                'region' => $message['content']['address_detail']['province'] ?? null,
                'city' => $message['content']['address_detail']['city'] ?? null,
                'isp' => $location[4] ?? null,
                'area' => $message['content']['address_detail']['street'] ?? null,
                'latitude' => $message['content']['point']['y'] ?? null,
                'longitude' => $message['content']['point']['x'] ?? null,
            ];
        }

        Log::warning('【百度IP库】返回错误信息：'.$ip.PHP_EOL.var_export($message, true));

        return null;
    }

    private static function baiduBce(string $ip): ?array
    { // 依据 https://qifu.baidu.com/?activeId=SEARCH_IP_ADDRESS&ip=&_frm=aladdin
        $client = self::http();
        $isIpv4 = self::isIpv4($ip);
        $url = $isIpv4
            ? "https://qifu-api.baidubce.com/ip/geo/v1/district?ip=$ip"
            : "https://qifu-api.baidubce.com/ip/geo/v1/ipv6/district?ip=$ip";

        $response = $client->get($url);
        if (! $response->ok()) {
            Log::error('【baiduBce】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['code'] === 'Success' && $data['ip'] === $ip) {
            $ipData = $data['data'] ?? null;
            if ($ipData) {
                return [
                    'country' => $ipData['country'] ?? null,
                    'region' => $ipData['prov'] ?? null,
                    'city' => $ipData['city'] ?? null,
                    'isp' => $ipData['isp'] ?: $ipData['owner'] ?? null,
                    'area' => $ipData['district'] ?? null,
                ];
            }
        }

        Log::error('【baiduBce】IP查询失败：'.($data['msg'] ?? 'unknown'));

        return null;
    }

    private static function ipGeoLocation(string $ip): ?array
    { // 开发依据: https://ipgeolocation.io/documentation.html
        $client = self::http();
        $lang = config('common.language.'.app()->getLocale().'.1');
        $response = $client->withHeader('Origin', 'https://ipgeolocation.io')
            ->get("https://api.ipgeolocation.io/ipgeo?ip=$ip&fields=country_name,state_prov,district,city,isp,latitude,longitude&lang=$lang");

        if (! $response->ok()) {
            Log::error('【ipGeoLocation】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }
        $data = $response->json();

        if ($data && $data['ip'] === $ip) {
            return [
                'country' => $data['country_name'] ?? null,
                'region' => $data['state_prov'] ?? null,
                'city' => $data['city'] ?? null,
                'isp' => $data['isp'] ?? null,
                'area' => $data['district'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];
        }

        Log::error('【ipgeolocation.io】IP查询失败：'.($data ?? 'unknown'));

        return null;
    }

    private static function TaoBao(string $ip): ?array
    { // 通过ip.taobao.com查询IP地址的详细信息 依据 https://ip.taobao.com/instructions 开发
        $client = self::http();
        $response = $client->post("https://ip.taobao.com/outGetIpInfo?ip=$ip&accessKey=alibaba-inc");
        if (! $response->ok()) {
            Log::error('【淘宝IP库】解析异常：'.$ip);

            return null;
        }

        $message = $response->json();
        $data = $message['data'] ?? null;
        if ($message['code'] === 0 && $data['ip'] === $ip) {
            // 简化三元表达式
            $fields = ['country', 'region', 'city', 'isp', 'area'];
            $result = [];
            foreach ($fields as $field) {
                $value = $data[$field] ?? null;
                $result[$field] = (isset($value) && strtolower($value) !== 'xx') ? $value : null;
            }

            return $result;
        }

        Log::warning('【淘宝IP库】返回错误信息：'.$ip.PHP_EOL.($message['msg'] ?? json_encode($message)));

        return null;
    }

    private static function speedtest(string $ip): ?array
    {
        $client = self::http();
        $response = $client->withHeaders(['Clientectype' => 65, 'Encrypt' => 'true'])->get('https://api-v3.speedtest.cn/ip', ['data' => base64_encode(openssl_encrypt(json_encode(['ip' => $ip], JSON_THROW_ON_ERROR), 'AES-128-CBC', '5ECC5D62140EC099', OPENSSL_RAW_DATA, 'E63EA892A702EEAA'
        ))]);
        if (! $response->ok()) {
            Log::error('【speedtest】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data['code'] === 0 && $data["'msg'"] === 'ok') {
            $ipData = json_decode(openssl_decrypt(base64_decode($data['data']), 'AES-128-CBC', '5ECC5D62140EC099', OPENSSL_RAW_DATA, 'E63EA892A702EEAA'), true, 512, JSON_THROW_ON_ERROR);

            if ($ipData['ip'] !== $ip) {
                Log::error('【speedtest】IP不一致，查询IP:'.$ip.' 返回IP:'.$ipData['ip'] ?? 'null');

                return null;
            }

            return [
                'country' => $ipData['country'] ?? null,
                'region' => $ipData['province'] ?? null,
                'city' => $ipData['city'] ?? null,
                'isp' => $ipData['isp'] ?: $ipData['operator'] ?? null,
                'area' => $ipData['district'] ?? null,
                'latitude' => $ipData['lat'] ?? null,
                'longitude' => $ipData['lon'] ?? null,
            ];
        }

        Log::error('【speedtest】IP查询失败');

        return null;
    }

    private static function juHe(string $ip): ?array
    { // 开发依据: https://www.juhe.cn/docs/api/id/1
        $client = self::http();
        $response = $client->asForm()->post('https://apis.juhe.cn/ip/Example/query.php', ['IP' => $ip]);
        if (! $response->ok()) {
            Log::error('【juHe】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }
        $data = $response->json();
        if ($data['resultcode'] === '200' && $data['error_code'] === 0) {
            $ipData = $data['result'];

            if ($ipData) {
                return [
                    'country' => $ipData['Country'] ?? null,
                    'region' => $ipData['Province'] ?? null,
                    'city' => $ipData['City'] ?? null,
                    'isp' => $ipData['Isp'] ?? null,
                    'area' => $ipData['District'] ?? null,
                ];
            }
        }

        return null;
    }

    private static function ip2Region(string $ip): ?array
    { // 通过ip2Region查询IP地址的详细信息 数据库不经常更新
        try {
            $data = (new XdbSearcher)->search($ip);
        } catch (Exception $e) {
            Log::error('【ip2Region】错误信息：'.$e->getMessage());

            return null;
        }

        if (! empty($data)) {
            $location = explode('|', $data);

            return [
                'country' => $location[0] ?? null,
                'region' => $location[2] ?? null,
                'city' => $location[3] ?? null,
                'isp' => $location[4] ?? null,
                'area' => $location[1] ?? null,
            ];
        }

        return null;
    }

    private static function IPDB(string $ip): array
    { // 通过IPDB格式的离线数据查询IP地址的详细信息 来源: https://github.com/metowolf/qqwry.ipdb
        $filePath = database_path('qqwry.ipdb');
        $location = (new City($filePath))->findMap($ip, 'CN');

        return [
            'country' => $location['country_name'] ?? null,
            'region' => $location['region_name'] ?? null,
            'city' => $location['city_name'] ?? null,
            'isp' => $location['isp_domain'] ?? null,
            'area' => null,
        ];
    }

    private static function IPSB(string $ip): ?array
    { // 通过api.ip.sb查询IP地址的详细信息
        $client = self::http();
        try {
            $response = $client->post("https://api.ip.sb/geoip/$ip");
            if (! $response->ok()) {
                Log::warning('[IPSB] 解析'.$ip.'异常: '.$response->body());

                return null;
            }

            $data = $response->json();
            if ($data && $data['ip'] && $data['ip'] === $ip) {
                return [
                    'country' => $data['country'] ?? null,
                    'region' => $data['region'] ?? null,
                    'city' => $data['city'] ?? null,
                    'isp' => $data['organization'] ?? null,
                    'area' => null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                ];
            }
        } catch (Exception $e) {
            Log::error('[IPSB] 解析'.$ip.'错误: '.var_export($e->getMessage(), true));
        }

        return null;
    }

    private static function ipinfo(string $ip): ?array
    { // 开发依据: https://ipinfo.io/account/home
        $client = self::http();
        $key = config('services.ip.ipinfo_token');
        if (empty($key)) {
            return null;
        }

        $response = $client->acceptJson()->get("https://ipinfo.io/$ip?token=$key");
        if (! $response->ok()) {
            Log::error('【ipinfo】解析异常：'.$ip);

            return null;
        }

        $data = $response->json();
        if ($data && $data['ip'] === $ip) {
            $location = explode(',', $data['loc'] ?? '');

            return [
                'country' => $data['country'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'isp' => $data['org'] ?? null,
                'area' => null,
                'latitude' => $location[0] ?? null,
                'longitude' => $location[1] ?? null,
            ];
        }

        return null;
    }

    private static function ip234(string $ip): ?array
    {
        $client = self::http();
        $response = $client->get("https://ip234.in/search_ip?ip=$ip");
        if (! $response->ok()) {
            Log::error('【ip234】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data['code'] === 0) {
            $ipData = $data['data'];
            if ($ipData && $ipData['ip'] === $ip) {
                return [
                    'country' => $ipData['country'] ?? null,
                    'region' => $ipData['region'] ?? null,
                    'city' => $ipData['city'] ?? null,
                    'isp' => $ipData['organization'] ?? null,
                    'area' => null,
                    'latitude' => $ipData['latitude'] ?? null,
                    'longitude' => $ipData['longitude'] ?? null,
                ];
            }
        }
        Log::error('【ip234】IP查询失败：'.($data['msg'] ?? 'unknown'));

        return null;
    }

    private static function dbIP(string $ip): ?array
    { // 开发依据: https://db-ip.com/api/doc.php
        $client = self::http();
        $response = $client->acceptJson()->get("https://api.db-ip.com/v2/free/$ip");
        if (! $response->ok()) {
            Log::error('【dbIP】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['ipAddress'] === $ip) {
            return [
                'country' => $data['countryName'] ?? null,
                'region' => $data['stateProv'] ?? null,
                'city' => $data['city'] ?? null,
                'isp' => null,
                'area' => null,
            ];
        }

        return null;
    }

    private static function IP2Online(string $ip): ?array
    { // 开发依据: https://www.ip2location.io/ip2location-documentation
        $client = self::http();
        $key = config('services.ip.IP2Location_key');
        if (empty($key)) {
            $response = $client->acceptJson()->get("https://api.ip2location.io/?ip=$ip");
        } else {
            $response = $client->acceptJson()->get("https://api.ip2location.io/?key=$key&ip=$ip");
        }
        if (! $response->ok()) {
            Log::error('【IP2Online】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['ip'] === $ip) {
            return [
                'country' => $data['country_name'] ?? null,
                'region' => $data['region_name'] ?? null,
                'city' => $data['city_name'] ?? null,
                'isp' => $data['as'] ?? null,
                'area' => null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];
        }

        return null;
    }

    private static function ipdata(string $ip): ?array
    { // 开发依据: https://docs.ipdata.co/docs
        $client = self::http();
        $key = config('services.ip.ipdata_key');
        if (empty($key)) {
            $response = $client->withHeader('Referer', 'https://ipdata.co/')->get("https://api.ipdata.co/$ip?api-key=dfaeafd1e8192e29db79905207d07059a81161c04fce90b040866b22&fields=ip,city,region,country_name,latitude,longitude,asn");
        } else {
            $response = $client->get("https://api.ipdata.co/$ip?api-key=$key&fields=ip,city,region,country_name,latitude,longitude,asn");
        }

        if (! $response->ok()) {
            Log::error('【ipdata】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['ip'] === $ip) {
            return [
                'country' => $data['country_name'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'isp' => $data['asn']['name'] ?? null,
                'area' => null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];
        }

        return null;
    }

    private static function ipApiCo(string $ip): ?array
    { // 开发依据: https://ipapi.co/api/
        $client = self::http();
        $response = $client->get("https://ipapi.co/$ip/json/");
        if (! $response->ok()) {
            Log::error('【ipApiCo】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['ip'] === $ip) {
            return [
                'country' => $data['country_name'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'isp' => $data['org'] ?? null,
                'area' => null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];
        }

        return null;
    }

    private static function ip2Location(string $ip): ?array
    { // 通过ip2Location查询IP地址的详细信息 来源: https://lite.ip2location.com/database-download
        $filePath = database_path('IP2LOCATION-LITE-DB11.IPV6.BIN');
        try {
            $location = (new Database($filePath, Database::FILE_IO))
                ->lookup($ip, [Database::CITY_NAME, Database::REGION_NAME, Database::COUNTRY_NAME, Database::LATITUDE, Database::LONGITUDE]);

            return [
                'country' => $location['countryName'] ?? null,
                'region' => $location['regionName'] ?? null,
                'city' => $location['cityName'] ?? null,
                'isp' => null,
                'area' => null,
                'latitude' => $location['latitude'] ?? null,
                'longitude' => $location['longitude'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('【ip2Location】错误信息：'.$e->getMessage());
        }

        return null;
    }

    private static function GeoIP2(string $ip): ?array
    { // 通过GeoIP2查询IP地址的详细信息 来源：https://github.com/P3TERX/GeoLite.mmdb/releases
        $filePath = database_path('GeoLite2-City.mmdb');
        try {
            $location = (new Reader($filePath))->city($ip);

            return [
                'country' => $location->country->name ?? null,
                'region' => $location->mostSpecificSubdivision->name ?? null,
                'city' => $location->city->name ?? null,
                'isp' => null,
                'area' => null,
                'latitude' => $location->location->latitude ?? null,
                'longitude' => $location->location->longitude ?? null,
            ];
        } catch (AddressNotFoundException $e) {
            Log::error("【GeoIP2】查询失败：$ip ".$e->getMessage());
        } catch (InvalidDatabaseException $e) {
            Log::error("【GeoIP2】数据库无效：$ip ".$e->getMessage());
        } catch (Exception $e) {
            Log::error("【GeoIP2】其他错误：$ip ".$e->getMessage());
        }

        return null;
    }

    private static function ipApiCom(string $ip): ?array
    {
        $client = self::http();
        $key = config('services.ip.ipApiCom_acess_key');
        if (empty($key)) {
            return null;
        }

        $response = $client->get("https://api.ipapi.com/api/$ip?access_key=$key");
        if (! $response->ok()) {
            Log::error('【ipApiCom】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['ip'] === $ip) {
            return [
                'country' => $data['country_name'] ?? null,
                'region' => $data['region_name'] ?? null,
                'city' => $data['city'] ?? null,
                'isp' => $data['connection']['isp'] ?? null,
                'area' => null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];
        }

        return null;
    }

    private static function vore(string $ip): ?array
    { // 开发依据: https://api.vore.top/
        $client = self::http();
        $response = $client->get("https://api.vore.top/api/IPdata?ip=$ip");
        if (! $response->ok()) {
            Log::error('【vore】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data['code'] === 200) {
            $ipData = $data['ipdata'];

            if ($ipData) {
                return [
                    'country' => $ipData['info1'] ?? null,
                    'region' => $ipData['info2'] ?? null,
                    'city' => $ipData['info3'] ?? null,
                    'isp' => $ipData['isp'] ?? null,
                    'area' => null,
                ];
            }
        }

        return null;
    }

    private static function ipw(string $ip): ?array
    { // 开发依据: https://ipw.cn/ip/ https://ipw.cn/ipv6/
        $client = self::http();
        $response = $client->withHeader('Referer', 'https://ipw.cn/')->get('https://rest.ipw.cn/api/aw/v1/ip'.(self::isIpv4($ip) ? 'v4' : 'v6')."?ip=$ip&warning=please-direct-use-please-use-ipplus360.com");
        if (! $response->ok()) {
            Log::error('【ipw】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['code'] === 'Success' && $data['ip'] === $ip) {
            $ipData = $data['data'];
            if ($ipData) {
                return [
                    'country' => $ipData['country'] ?? null,
                    'region' => $ipData['prov'] ?? null,
                    'city' => $ipData['city'] ?? null,
                    'isp' => $ipData['isp'] ?? null,
                    'area' => $ipData['district'] ?? null,
                    'latitude' => $ipData['lat'] ?? null,
                    'longitude' => $ipData['lng'] ?? null,
                ];
            }
        }

        return null;
    }

    private static function bjjii(string $ip): ?array
    { // 开发依据: https://api.bjjii.com/doc/77
        $client = self::http();
        $key = config('services.ip.bjjii_key');
        if (empty($key)) {
            return null;
        }
        $response = $client->get("https://api.bjjii.com/api/ip/query?key=$key&ip=$ip");
        if (! $response->ok()) {
            Log::error('【bjjii】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data['code'] === 200 && $data['data']['ip'] === $ip) {
            $ipData = $data['data']['info'];

            if ($ipData) {
                return [
                    'country' => $ipData['nation'] ?? null,
                    'region' => $ipData['province'] ?? null,
                    'city' => $ipData['city'] ?? null,
                    'isp' => $ipData['isp'] ?? null,
                    'area' => $ipData['district'] ?? null,
                    'latitude' => $ipData['lat'] ?? null,
                    'longitude' => $ipData['lng'] ?? null,
                ];
            }
        }

        return null;
    }

    private static function pconline(string $ip): ?array
    { // ipv4 only
        $client = self::http();

        $response = $client->get("https://whois.pconline.com.cn/ipJson.jsp?ip=$ip&json=true");
        $data = json_decode(mb_convert_encoding($response->body(), 'UTF-8', 'GBK'), true, 512, JSON_THROW_ON_ERROR);
        if (! $response->ok()) {
            Log::error('【pconline】查询无效：'.$ip.var_export($data, true));

            return null;
        }

        if ($data && $data['ip'] === $ip) {
            return [
                'country' => null,
                'region' => $data['pro'] ?? null,
                'city' => $data['city'] ?? null,
                'isp' => null,
                'area' => $data['region'] ?? null,
            ];
        }

        Log::error('【pconline】IP查询失败：'.($data['msg'] ?? 'unknown'));

        return null;
    }

    private static function ipApiIO(string $ip): ?array
    { // 开发依据: https://ip-api.io/
        $client = self::http();
        $response = $client->get("https://ip-api.io/api/v1/ip/$ip");
        if (! $response->ok()) {
            Log::error('【ipApiIO】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['ip'] === $ip) {
            $ipData = $data['location'];

            if ($ipData) {
                return [
                    'country' => $ipData['country'] ?? null,
                    'region' => null,
                    'city' => $ipData['city'] ?? null,
                    'isp' => null,
                    'area' => null,
                    'latitude' => $ipData['latitude'] ?? null,
                    'longitude' => $ipData['longitude'] ?? null,
                ];
            }
        }

        return null;
    }

    private static function ipApiIS(string $ip): ?array
    {
        $client = self::http();
        $response = $client->get("https://api.ipapi.is/?ip=$ip");
        if (! $response->ok()) {
            Log::error('【ipApiIS】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['ip'] === $ip) {
            $ipData = $data['location'];

            if ($ipData) {
                return [
                    'country' => $ipData['country'] ?? null,
                    'region' => $ipData['state'] ?? null,
                    'city' => $ipData['city'] ?? null,
                    'isp' => $data['asn']['org'] ?? null,
                    'area' => null,
                    'latitude' => $ipData['latitude'] ?? null,
                    'longitude' => $ipData['longitude'] ?? null,
                ];
            }
        }

        return null;
    }

    private static function freeipapi(string $ip): ?array
    {
        $client = self::http();
        $response = $client->get("https://free.freeipapi.com/api/json/$ip");
        if (! $response->ok()) {
            Log::error('【freeipapi】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['ipAddress'] === $ip) {
            return [
                'country' => $data['countryName'] ?? null,
                'region' => $data['regionName'] ?? null,
                'city' => $data['cityName'] ?? null,
                'isp' => $data['asnOrganization'] ?? null,
                'area' => null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];
        }

        return null;
    }

    private static function ipwhois(string $ip): ?array
    {
        $client = self::http();
        $response = $client->get("https://ipwhois.app/json/$ip?format=json");
        if (! $response->ok()) {
            Log::error('【ipwhois】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        if ($data && $data['success'] && $data['ip'] === $ip) {
            return [
                'country' => $data['country'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'isp' => $data['isp'] ?? null,
                'area' => null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];
        }

        return null;
    }
}
