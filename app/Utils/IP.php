<?php

namespace App\Utils;

use Arr;
use Cache;
use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;
use IP2Location\Database;
use ipip\db\City;
use Log;
use MaxMind\Db\Reader\InvalidDatabaseException;
use XdbSearcher;

class IP
{
    /**
     * 缓存键前缀.
     *
     * 不能用 Cache::tags()：只有 redis/memcached/array 这些驱动支持打标签，
     * 而 file（config 里的默认值）、database 会直接抛 BadMethodCallException，
     * 一旦踩上就是所有调用 IP 查询的地方（登录、后台列表、中间件）全部 500。
     */
    public const CACHE_PREFIX = 'ip_geo:';

    /** 单个查询的超时（秒）：并发之后整体耗时由最慢的一个决定，所以不要设大 */
    private const TIMEOUT = 5;

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36';

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
            $cached = Cache::get(self::CACHE_PREFIX.$ip);
            if ($cached && ! empty(array_filter($cached))) {
                return $cached;
            }

            $isIpv4 = self::isIpv4($ip);
            if (app()->getLocale() === 'zh_CN') {
                $checkers = $isIpv4
                    ? ['ipApi', 'Baidu', 'ipGeoLocation', 'TaoBao', 'speedtest', 'bjjii', 'vore', 'juHe', 'ip2Region', 'IPDB', 'ipwhois', 'pconline']
                    : ['ipApi', 'Baidu', 'ipGeoLocation', 'vore', 'ip2Region'];
            } else {
                $checkers = ['ipApi', 'IPSB', 'ipinfo', 'ip234', 'ipGeoLocation', 'dbIP', 'IP2Online', 'ipdata', 'ipApiIS', 'ipApiCo', 'ip2Location', 'GeoIP2', 'ipApiCom', 'freeipapi'];
            }

            $result = self::IPLookup($ip, $checkers);
        }

        if ($result !== null) {
            $result['address'] = implode(' ', Arr::except(array_filter($result), ['isp', 'latitude', 'longitude']));
            Cache::put(self::CACHE_PREFIX.$ip, $result, Day);
        }

        return $result;
    }

    private static function IPLookup(string $ip, array $checkers): ?array
    {
        // 先把名单里的 HTTP provider 并发发出去，再按优先级取第一个成功的结果：
        // 逐个串行时最坏要等所有超时之和，并发之后只等最慢的那一个。
        $responses = self::concurrentResponses($ip, $checkers);

        foreach ($checkers as $checker) {
            if (! method_exists(self::class, $checker)) {
                continue;
            }

            try {
                // 只有拿到并发响应的 provider 才多传一个参数；离线库（ip2Region/IPDB/ip2Location/GeoIP2）保持原签名
                $result = call_user_func_array([self::class, $checker], isset($responses[$checker]) ? [$ip, $responses[$checker]] : [$ip]);
                if (is_array($result) && ! empty(array_filter($result))) {
                    return $result;
                }
            } catch (Exception $e) {
                Log::error("[$checker] IP信息获取报错: ".$e->getMessage());
            }
        }

        return null;
    }

    /**
     * 并发预取名单里的 HTTP provider.
     *
     * @return array<string, Response> provider 名 => 响应；没配 key 的与传输层失败的都不在其中
     */
    private static function concurrentResponses(string $ip, array $checkers): array
    {
        $requests = array_intersect_key(self::requests($ip), array_flip($checkers));

        if (count($requests) < 2) { // 只有一个候选时并发没有意义，让它自己老老实实发一次
            return [];
        }

        try {
            $responses = Http::pool(function (Pool $pool) use ($requests) {
                foreach ($requests as $name => $request) {
                    // 请求定义必须在真的要发时才调用惰性客户端：pool 里注册了却从未发送的通道，
                    // 会让 Http::pool() 在收集结果时对 null promise 调用 wait() 而报错
                    $request(static fn (): PendingRequest => self::withDefaults($pool->as($name)));
                }
            });
        } catch (Exception $e) {
            // 任何一个请求在传输层炸掉都会带走整批结果，此时退回逐个获取（慢，但不会丢数据）
            Log::warning('IP并发查询失败，退回逐个获取: '.$e->getMessage());

            return [];
        }

        // 传输失败的通道拿到的是异常对象：换成 500 响应，让 provider 直接判失败，
        // 否则它会带着 retry(2) 再把死掉的地址打一遍
        return array_map(static fn ($response): Response => $response instanceof Response ? $response : new Response(new Psr7Response(500)), $responses);
    }

    /**
     * provider 名 => 请求定义.
     *
     * 参数是「惰性客户端」：顺序执行时给出统一的 HTTP 客户端，并发时给出 pool 里的通道。
     * 返回 null 表示这次不该发请求（例如没配 key），此时 provider 自己的老门会拦住它。
     *
     * 返回值不强标注：顺序执行时是 Response（send() 会断言），并发时是 Guzzle 的 Promise，
     * 由 Pool 自己持有并等待。
     *
     * @return array<string, callable(callable(): PendingRequest): mixed>
     */
    private static function requests(string $ip): array
    {
        return [
            'ipApi' => static function (callable $client) use ($ip) {
                $lang = str_replace('_', '-', app()->getLocale());
                $key = config('services.ip.ip-api_key');
                $host = empty($key) ? 'https://demo.ip-api.com' : 'https://pro.ip-api.com';

                return $client()->withHeader('Origin', 'https://members.ip-api.com')->get("$host/json/$ip?fields=582361&key=$key&lang=$lang");
            },
            'Baidu' => static function (callable $client) use ($ip) {
                $key = config('services.ip.baidu_ak');
                if (empty($key)) {
                    return null;
                }

                return $client()->get("https://api.map.baidu.com/location/ip?ak=$key&ip=$ip&coor=gcj02");
            },
            'ipGeoLocation' => static function (callable $client) use ($ip) {
                $lang = config('common.language.'.app()->getLocale().'.1');

                return $client()->withHeader('Origin', 'https://ipgeolocation.io')
                    ->get("https://api.ipgeolocation.io/ipgeo?ip=$ip&fields=country_name,state_prov,district,city,isp,latitude,longitude&lang=$lang");
            },
            'TaoBao' => static fn (callable $client) => $client()->post("https://ip.taobao.com/outGetIpInfo?ip=$ip&accessKey=alibaba-inc"),
            'speedtest' => static fn (callable $client) => $client()->withHeaders(['Clientectype' => 65, 'Encrypt' => 'true'])->get('https://api-v3.speedtest.cn/ip', ['data' => base64_encode(openssl_encrypt(json_encode(['ip' => $ip], JSON_THROW_ON_ERROR), 'AES-128-CBC', '5ECC5D62140EC099', OPENSSL_RAW_DATA, 'E63EA892A702EEAA'))]),
            'juHe' => static fn (callable $client) => $client()->asForm()->post('https://apis.juhe.cn/ip/Example/query.php', ['IP' => $ip]),
            // ip.sb 自 2024 起明确拒绝 POST（响应体里会写明 "API no longer supports POST requests"），必须用 GET
            'IPSB' => static fn (callable $client) => $client()->get("https://api.ip.sb/geoip/$ip"),
            'ipinfo' => static function (callable $client) use ($ip) {
                $key = config('services.ip.ipinfo_token');
                if (empty($key)) {
                    return null;
                }

                return $client()->acceptJson()->get("https://ipinfo.io/$ip?token=$key");
            },
            'ip234' => static fn (callable $client) => $client()->get("https://ip234.in/search_ip?ip=$ip"),
            'dbIP' => static fn (callable $client) => $client()->acceptJson()->get("https://api.db-ip.com/v2/free/$ip"),
            'IP2Online' => static function (callable $client) use ($ip) {
                $key = config('services.ip.IP2Location_key');

                return empty($key)
                    ? $client()->acceptJson()->get("https://api.ip2location.io/?ip=$ip")
                    : $client()->acceptJson()->get("https://api.ip2location.io/?key=$key&ip=$ip");
            },
            'ipdata' => static function (callable $client) use ($ip) {
                $key = config('services.ip.ipdata_key');
                $fields = 'ip,city,region,country_name,latitude,longitude,asn';

                return empty($key)
                    ? $client()->withHeader('Referer', 'https://ipdata.co/')->get("https://api.ipdata.co/$ip?api-key=dfaeafd1e8192e29db79905207d07059a81161c04fce90b040866b22&fields=$fields")
                    : $client()->get("https://api.ipdata.co/$ip?api-key=$key&fields=$fields");
            },
            'ipApiCo' => static fn (callable $client) => $client()->get("https://ipapi.co/$ip/json/"),
            'ipApiCom' => static function (callable $client) use ($ip) {
                $key = config('services.ip.ipApiCom_acess_key');
                if (empty($key)) {
                    return null;
                }

                return $client()->get("https://api.ipapi.com/api/$ip?access_key=$key");
            },
            'vore' => static fn (callable $client) => $client()->get("https://api.vore.top/api/IPdata?ip=$ip"),
            'bjjii' => static function (callable $client) use ($ip) {
                $key = config('services.ip.bjjii_key');
                if (empty($key)) {
                    return null;
                }

                return $client()->get("https://api.bjjii.com/api/ip/query?key=$key&ip=$ip");
            },
            'pconline' => static fn (callable $client) => $client()->get("https://whois.pconline.com.cn/ipJson.jsp?ip=$ip&json=true"),
            'ipApiIS' => static fn (callable $client) => $client()->get("https://api.ipapi.is/?ip=$ip"),
            'freeipapi' => static fn (callable $client) => $client()->get("https://free.freeipapi.com/api/json/$ip"),
            'ipwhois' => static fn (callable $client) => $client()->get("https://ipwhois.app/json/$ip?format=json"),
        ];
    }

    /** 按名字取出请求定义并立即发送（顺序执行路径） */
    private static function send(string $name, string $ip): Response
    {
        $request = self::requests($ip)[$name] ?? throw new InvalidArgumentException("未定义的IP请求: $name");

        return $request(static fn (): PendingRequest => self::http());
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
            $ret = self::IPLookup($ip, ['IPSB', 'ipApi', 'ipinfo', 'IP2Online', 'speedtest', 'bjjii', 'Baidu', 'ip234', 'ipdata', 'ipGeoLocation', 'ipApiIS', 'ipApiCo', 'ipApiCom', 'ip2Location', 'ipwhois', 'freeipapi']);
        }

        if (is_array($ret)) {
            return Arr::only($ret, ['latitude', 'longitude']);
        }

        return false;
    }

    private static function http(): PendingRequest
    { // 统一的HTTP客户端方法
        // 每次都新建：PendingRequest 是可变的，共用一个实例会让前面的 provider 留下的
        // 请求头 / 选项（Origin、Referer、asForm、acceptJson…）污染后面的 provider。
        return self::withDefaults(Http::timeout(self::TIMEOUT));
    }

    /**
     * 统一的请求默认值，顺序执行与并发（pool）两条路径共用.
     *
     * 注意 retry(2) 只在顺序执行时生效：Http::pool() 走的是 async 通道，不经过重试包装。
     */
    private static function withDefaults(PendingRequest $request): PendingRequest
    {
        return $request->timeout(self::TIMEOUT)->retry(2)->withOptions(['http_errors' => false])->withoutVerifying()->withUserAgent(self::USER_AGENT);
    }

    private static function ipApi(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://ip-api.com/docs/api:json
        $response ??= self::send(__FUNCTION__, $ip);

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

    private static function Baidu(string $ip, ?Response $response = null): ?array
    { // 通过api.map.baidu.com查询IP地址的详细信息，依据 http://lbsyun.baidu.com/index.php?title=webapi/ip-api 开发
        if (empty(config('services.ip.baidu_ak'))) { // 没配 key 直接跳过；requests() 里同样要判断，那里是给并发路径用的
            return null;
        }

        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function ipGeoLocation(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://ipgeolocation.io/documentation.html
        $response ??= self::send(__FUNCTION__, $ip);

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

    private static function TaoBao(string $ip, ?Response $response = null): ?array
    { // 通过ip.taobao.com查询IP地址的详细信息 依据 https://ip.taobao.com/instructions 开发
        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function speedtest(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://api-v3.speedtest.cn/ 共用 https://www.speedtest.cn/ 的查询接口
        $response ??= self::send(__FUNCTION__, $ip);
        if (! $response->ok()) {
            Log::error('【speedtest】查询无效：'.$ip.var_export($response->json(), true));

            return null;
        }

        $data = $response->json();
        // 注意是 $data['msg']：这里曾经写成 $data["'msg'"]（带引号的键名），导致该 provider 永远取不到数据
        if (($data['code'] ?? null) !== 0 || ($data['msg'] ?? null) !== 'ok') {
            Log::error('【speedtest】IP查询失败：'.($data['msg'] ?? 'unknown'));

            return null;
        }

        $ipData = $data['data'] ?? null;
        // 带 Encrypt 头时返回 AES-128-CBC 加密的 base64，否则直接是明文对象（两种都要支持）
        if (is_string($ipData)) {
            $plain = openssl_decrypt(base64_decode($ipData), 'AES-128-CBC', '5ECC5D62140EC099', OPENSSL_RAW_DATA, 'E63EA892A702EEAA');
            $ipData = $plain === false ? null : json_decode($plain, true);
        }

        if (! is_array($ipData) || ($ipData['ip'] ?? null) !== $ip) {
            Log::error('【speedtest】IP不一致，查询IP:'.$ip.' 返回IP:'.($ipData['ip'] ?? 'null'));

            return null;
        }

        return [
            'country' => $ipData['country'] ?? null,
            'region' => $ipData['province'] ?? null,
            'city' => $ipData['city'] ?? null,
            'isp' => $ipData['isp'] ?: $ipData['operator'] ?? null,
            'area' => $ipData['district'] ?? null,
            'latitude' => $ipData['lat'] ?? null,
            'longitude' => $ipData['lng'] ?? ($ipData['lon'] ?? null),
        ];
    }

    private static function juHe(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://www.juhe.cn/docs/api/id/1
        $response ??= self::send(__FUNCTION__, $ip);
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
            // 随包这份 xdb 少了官方的「区域」段（国家|区域|省份|城市|ISP），按段数补位才能对齐省/市
            if (count($location) === 4) {
                array_splice($location, 1, 0, [null]);
            }
            // 库里的未知字段写的是字面量 0，归一成 null
            $location = array_map(static fn (?string $field): ?string => $field === null || $field === '' || $field === '0' ? null : $field, $location);

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

    private static function IPSB(string $ip, ?Response $response = null): ?array
    { // 通过api.ip.sb查询IP地址的详细信息
        try {
            $response ??= self::send(__FUNCTION__, $ip);
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

    private static function ipinfo(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://ipinfo.io/account/home
        if (empty(config('services.ip.ipinfo_token'))) { // 没配 token 直接跳过；requests() 里同样要判断
            return null;
        }

        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function ip234(string $ip, ?Response $response = null): ?array
    {
        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function dbIP(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://db-ip.com/api/doc.php
        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function IP2Online(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://www.ip2location.io/ip2location-documentation
        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function ipdata(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://docs.ipdata.co/docs
        $response ??= self::send(__FUNCTION__, $ip);

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

    private static function ipApiCo(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://ipapi.co/api/
        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function ipApiCom(string $ip, ?Response $response = null): ?array
    {
        if (empty(config('services.ip.ipApiCom_acess_key'))) { // 没配 key 直接跳过；requests() 里同样要判断
            return null;
        }

        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function vore(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://api.vore.top/
        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function bjjii(string $ip, ?Response $response = null): ?array
    { // 开发依据: https://api.bjjii.com/doc/77
        if (empty(config('services.ip.bjjii_key'))) { // 没配 key 直接跳过；requests() 里同样要判断
            return null;
        }

        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function pconline(string $ip, ?Response $response = null): ?array
    { // ipv4 only
        $response ??= self::send(__FUNCTION__, $ip);

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

    private static function ipApiIS(string $ip, ?Response $response = null): ?array
    {
        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function freeipapi(string $ip, ?Response $response = null): ?array
    {
        $response ??= self::send(__FUNCTION__, $ip);
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

    private static function ipwhois(string $ip, ?Response $response = null): ?array
    {
        $response ??= self::send(__FUNCTION__, $ip);
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
