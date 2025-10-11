<?php

namespace App\Utils;

use Exception;
use Http;
use Illuminate\Http\Client\PendingRequest;
use Log;

class NetworkDetection
{
    private const PROTOCOLS = ['icmp', 'tcp'];

    private const STATUS_OK = 1; // 正常

    private const STATUS_ABROAD = 2; // 国外访问异常

    private const STATUS_BLOCKED = 3; // 被墙

    private const STATUS_DOWN = 4; // 宕机

    private static array $apis = ['selfHost', 'vps234', 'idcoffer', 'ip112', 'upx8', 'rss', 'vps1352'];

    private static ?PendingRequest $basicRequest;

    public static function networkStatus(string $ip, int $port = 22, ?string $source = null): ?array
    {
        $status = self::checkNetworkStatus($ip, $port, $source);

        if (! $status) {
            return null;
        }

        $result = [];
        foreach (self::PROTOCOLS as $protocol) {
            [$in, $out] = [$status['in'][$protocol], $status['out'][$protocol]];

            $result[$protocol] = match (true) {
                $in && $out => self::STATUS_OK,
                $in && ! $out => self::STATUS_ABROAD,
                ! $in && $out => self::STATUS_BLOCKED,
                default => self::STATUS_DOWN,
            };
        }

        return $result;
    }

    private static function checkNetworkStatus(string $ip, int $port, ?string $source = null): ?array
    { // 通过众多API进行节点阻断检测.
        self::$basicRequest = Http::timeout(5)->retry(2)->withOptions(['http_errors' => false])->withoutVerifying()->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36');

        foreach ($source ? [$source] : self::$apis as $api) {
            if (! method_exists(self::class, $api)) {
                continue;
            }

            try {
                $result = self::$api($ip, $port);
                if ($result !== null) {
                    return $result;
                }
            } catch (Exception $e) {
                Log::error("[$api] 网络阻断测试报错: ".$e->getMessage());
            }
        }

        return null;
    }

    private static function toolsdaquan(string $ip, int $port): ?array
    { // deprecated, 开发依据: https://www.toolsdaquan.com/ipcheck/
        $data = self::fetchJson(static fn () => self::$basicRequest->withHeader('Referer', 'https://www.toolsdaquan.com/ipcheck/')
            ->get("https://www.toolsdaquan.com/toolapi/public/ipchecking?ip=$ip&port=$port"), $ip, 'toolsdaquan');

        if (! $data || $data['success'] !== 1) {
            return null;
        }

        $data = $data['data'];

        return [
            'in' => ['icmp' => $data['icmp'] === 'success', 'tcp' => $data['tcp'] === 'success'],
            'out' => ['icmp' => $data['outside_icmp'] === 'success', 'tcp' => $data['outside_tcp'] === 'success'],
        ];
    }

    private static function fetchJson(callable $callback, string $ip, string $apiName): ?array
    {
        try {
            $res = $callback();
            if ($res->ok()) {
                return $res->json();
            }
        } catch (Exception $e) {
            Log::warning("【阻断检测】检测{$ip}时, [$apiName]接口异常: ".$e->getMessage());
        }

        return null;
    }

    private static function gd(string $ip, int $port): ?array
    { // deprecated, 开发依据: https://ping.gd/
        $data = self::fetchJson(static fn () => self::$basicRequest->get("https://ping.gd/api/ip-test/$ip:$port"), $ip, 'gd');
        if (! $data) {
            return null;
        }

        return [
            'in' => [
                'icmp' => $data[0]['result']['ping_alive'],
                'tcp' => $data[0]['result']['telnet_alive'],
            ],
            'out' => [
                'icmp' => $data[1]['result']['ping_alive'],
                'tcp' => $data[1]['result']['telnet_alive'],
            ],
        ];
    }

    private static function vps234(string $ip, int $port): ?array
    { // 开发依据: https://www.vps234.com/ipchecker/
        $data = self::fetchJson(static fn () => self::$basicRequest
            ->withHeaders(['Origin' => 'https://www.vps234.com', 'Referer' => 'https://www.vps234.com/ipchecker/'])
            ->asForm()->post('https://www.vps234.com/ipcheck/getdata/', ['ip' => $ip]), $ip, 'vps234');
        if (! $data || $data['error'] || ! ($data['data']['success'] ?? false)) {
            return null;
        }
        $r = $data['data']['data'];

        return [
            'in' => ['icmp' => $r['innerICMP'], 'tcp' => $r['innerTCP']],
            'out' => ['icmp' => $r['outICMP'], 'tcp' => $r['outTCP']],
        ];
    }

    private static function flyzy2005(string $ip, int $port): ?array
    { // deprecated, 开发依据: https://www.flyzy2005.cn/tech/ip-check/
        $inner = self::fetchJson(static fn () => self::$basicRequest->get("https://mini.flyzy2005.cn/ip_check.php?ip=$ip&port=$port"), $ip, 'flyzy2005');
        $outer = self::fetchJson(static fn () => self::$basicRequest->get("https://mini.flyzy2005.cn/ip_check_outside.php?ip=$ip&port=$port"), $ip, 'flyzy2005');

        if (! $inner || ! $outer) {
            return null;
        }

        return [
            'in' => ['icmp' => $inner['icmp'] === 'success', 'tcp' => $inner['tcp'] === 'success'],
            'out' => ['icmp' => $outer['outside_icmp'] === 'success', 'tcp' => $outer['outside_tcp'] === 'success'],
        ];
    }

    private static function idcoffer(string $ip, int $port): ?array
    { // 开发依据: https://www.idcoffer.com/ipcheck
        $inner = self::fetchJson(static fn () => self::$basicRequest->get("https://api.24kplus.com/ipcheck?host=$ip&port=$port"), $ip, 'idcoffer');
        $outer = self::fetchJson(static fn () => self::$basicRequest->get("https://api.idcoffer.com/ipcheck?host=$ip&port=$port"), $ip, 'idcoffer');

        if (! $inner || ! $outer || ! $inner['code'] || ! $outer['code']) {
            return null;
        }

        return [
            'in' => ['icmp' => $inner['data']['ping'], 'tcp' => $inner['data']['tcp']],
            'out' => ['icmp' => $outer['data']['ping'], 'tcp' => $outer['data']['tcp']],
        ];
    }

    private static function ip112(string $ip, int $port): ?array
    { // 开发依据: https://ip112.cn/
        $inner = self::fetchJson(static fn () => self::$basicRequest->asForm()->post('https://api.ycwxgzs.com/ipcheck/index.php', ['ip' => $ip, 'port' => $port]), $ip, 'ip112');
        $outer = self::fetchJson(static fn () => self::$basicRequest->asForm()->post('https://api.52bwg.com/ipcheck/ipcheck.php', ['ip' => $ip, 'port' => $port]), $ip, 'ip112');

        if (! $inner || ! $outer) {
            return null;
        }

        return [
            'in' => ['icmp' => str_contains($inner['icmp'], 'green'), 'tcp' => str_contains($inner['tcp'], 'green')],
            'out' => ['icmp' => str_contains($outer['icmp'], 'green'), 'tcp' => str_contains($outer['tcp'], 'green')],
        ];
    }

    private static function upx8(string $ip, int $port): ?array
    { // 开发依据: https://blog.upx8.com/ipcha.html
        $inner = self::fetchJson(static fn () => self::$basicRequest->asForm()->post('https://api.sm171.com/check-cn.php', ['ip' => $ip, 'port' => $port]), $ip, 'upx8');
        $outer = self::fetchJson(static fn () => self::$basicRequest->asForm()->post('https://ip.upx8.com/api/check-us.php', ['ip' => $ip, 'port' => $port]), $ip, 'upx8');

        if (! $inner || ! $outer) {
            return null;
        }

        return [
            'in' => ['icmp' => $inner['icmp'] === '正常', 'tcp' => $inner['tcp'] === '正常'],
            'out' => ['icmp' => $outer['icmp'] === '正常', 'tcp' => $outer['tcp'] === '正常'],
        ];
    }

    private static function vps1352(string $ip, int $port): ?array
    { // 开发依据: https://www.51vps.info/ipcheck.html https://www.vps1352.com/ipcheck.html 有缺陷api,查不了海外做判断 备用
        try {
            $response = self::$basicRequest->asForm()->withHeader('Referer', 'https://www.51vps.info')
                ->post('https://www.vps1352.com/check.php', ['ip' => $ip, 'port' => $port]);

            if ($response->ok()) {
                // 检查响应内容是否包含PHP错误信息
                $body = $response->body();
                if (str_contains($body, 'Warning') || str_contains($body, 'Fatal error')) {
                    // 如果响应中包含PHP错误信息，则提取JSON部分
                    $jsonStart = strpos($body, '{');
                    $jsonEnd = strrpos($body, '}');
                    if ($jsonStart !== false && $jsonEnd !== false) {
                        $jsonStr = substr($body, $jsonStart, $jsonEnd - $jsonStart + 1);
                        $data = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);
                    } else {
                        Log::warning("【阻断检测】检测{$ip}时，[vps1352]接口返回内容包含错误信息: ".$body);

                        return null;
                    }
                } else {
                    $data = $response->json();
                }

                if (! empty($data)) {
                    return [
                        'in' => ['icmp' => $data['icmp'] === '开放', 'tcp' => $data['tcp'] === '开放'],
                        'out' => ['icmp' => true, 'tcp' => true],
                    ];
                }
            }
        } catch (Exception $e) {
            Log::warning("【阻断检测】检测{$ip}时，[vps1352]接口异常: ".$e->getMessage());
        }

        return null;
    }

    private static function rss(string $ip, int $port): ?array
    { // 开发依据: https://ip.rss.ink/index/check
        $inner = self::fetchJson(static fn () => self::$basicRequest->get("https://ip.rss.ink/api/scan?ip=$ip&port=$port"), $ip, 'rss');
        $outer = self::fetchJson(static fn () => self::$basicRequest->get("https://tcp.mk/api/scan?ip=$ip&port=$port"), $ip, 'rss');

        if (! $inner || ! $outer || $inner['code'] !== 200 || $outer['code'] !== 200) {
            return null;
        }

        return [
            'in' => ['icmp' => $inner['msg'] === 'Ok', 'tcp' => $inner['msg'] === 'Ok'],
            'out' => ['icmp' => $outer['msg'] === 'Ok', 'tcp' => $outer['msg'] === 'Ok'],
        ];
    }

    private static function selfHost(string $ip, int $port): ?array
    { // 开发依据: https://github.com/ProxyPanel/PingAgent
        // 从环境变量获取探针服务器配置
        $domestic = config('services.probe.domestic');
        $foreign = config('services.probe.foreign');
        if (empty($domestic)) {
            return null;
        }

        $probe = static fn ($entry) => (static function ($entry) use ($ip, $port) {
            [$url,$token] = array_pad(explode('|', $entry), 2, null);
            $req = self::$basicRequest;
            if ($token) {
                $req = $req->withToken($token);
            }
            $res = $req->asJson()->post("$url/probe", ['target' => $ip, 'port' => $port]);
            $d = $res->ok() && ! empty($res->json()[0]) ? $res->json()[0] : [];

            return ['icmp' => ! empty($d['icmp']), 'tcp' => ! empty($d['tcp'])];
        })($entry);

        return ['in' => $probe($domestic), 'out' => empty($foreign) ? ['icmp' => true, 'tcp' => true] : $probe($foreign)];
    }
}
