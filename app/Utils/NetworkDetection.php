<?php

namespace App\Utils;

use Exception;
use Http;
use Illuminate\Http\Client\PendingRequest;
use Log;

class NetworkDetection
{
    private static PendingRequest $basicRequest;

    public function networkStatus(string $ip, int $port): ?array
    {
        $status = $this->networkCheck($ip, $port);
        if ($status) {
            $ret = [];
            foreach (['icmp', 'tcp'] as $protocol) {
                if ($status['in'][$protocol] && $status['out'][$protocol]) {
                    $check = 1; // 正常
                }

                if ($status['in'][$protocol] && ! $status['out'][$protocol]) {
                    $check = 2; // 国外访问异常
                }

                if (! $status['in'][$protocol] && $status['out'][$protocol]) {
                    $check = 3; // 被墙
                }

                $ret[$protocol] = $check ?? 4; // 服务器宕机
            }

            return $ret;
        }

        return null;
    }

    private function networkCheck(string $ip, int $port): ?array
    { // 通过众多API进行节点阻断检测.
        $checkers = ['toolsdaquan', 'vps234', 'flyzy2005', 'idcoffer', 'ip112', 'upx8', 'rss', 'gd', 'vps1352', 'akile'];
        self::$basicRequest = Http::timeout(15)->retry(2)->withOptions(['http_errors' => false])->withoutVerifying()->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36');

        foreach ($checkers as $checker) {
            try {
                if (method_exists(self::class, $checker)) {
                    $result = $this->$checker($ip, $port);
                    if ($result !== null) {
                        return $result;
                    }
                }
            } catch (Exception $e) {
                Log::error("[$checker] 网络阻断测试报错: ".$e->getMessage());

                continue;
            }
        }

        return null;
    }

    private function toolsdaquan(string $ip, int $port): ?array
    { // 开发依据: https://www.toolsdaquan.com/ipcheck/
        $response_inner = self::$basicRequest->withHeader('Referer', 'https://www.toolsdaquan.com/ipcheck/')->get("https://www.toolsdaquan.com/toolapi/public/ipchecking/$ip/$port");
        $response_outer = self::$basicRequest->withHeader('Referer', 'https://www.toolsdaquan.com/ipcheck/')->get("https://www.toolsdaquan.com/toolapi/public/ipchecking2/$ip/$port");

        if ($response_inner->ok() && $response_outer->ok()) {
            return $this->common_detection($response_inner->json(), $response_outer->json(), $ip);
        }

        return null;
    }

    private function common_detection(array $inner, array $outer, string $ip): ?array
    {
        if (empty($inner) || empty($outer)) {
            Log::warning("【阻断检测】检测{$ip}时，接口返回异常");

            return null;
        }

        return [
            'in' => [
                'icmp' => $inner['icmp'] === 'success',
                'tcp' => $inner['tcp'] === 'success',
            ],
            'out' => [
                'icmp' => $outer['outside_icmp'] === 'success',
                'tcp' => $outer['outside_tcp'] === 'success',
            ],
        ];
    }

    private function akile(string $ip, int $port): ?array
    { // 开发依据: https://tools.akile.io/
        $response = self::$basicRequest->get("https://tools.akile.io/gping?address=$ip&port=$port");

        if ($response->ok()) {
            $data = $response->json();
            if ($data) {
                return [
                    'in' => [
                        'icmp' => $data['cn_icmp'],
                        'tcp' => $data['cn_tcp'],
                    ],
                    'out' => [
                        'icmp' => $data['global_icmp'],
                        'tcp' => $data['global_tcp'],
                    ],
                ];
            }
        }
        Log::warning("【阻断检测】检测{$ip}时，[akile]接口返回异常");

        return null;
    }

    private function gd(string $ip, int $port): ?array
    { // 开发依据: https://ping.gd/
        $response = self::$basicRequest->get("https://ping.gd/api/ip-test/$ip:$port");

        if ($response->ok()) {
            $data = $response->json();
            if ($data) {
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
        }
        Log::warning("【阻断检测】检测{$ip}时，[ping.gd]接口返回异常");

        return null;
    }

    private function vps234(string $ip): ?array
    { // 开发依据: https://www.vps234.com/ipchecker/
        $response = self::$basicRequest->asForm()->post('https://www.vps234.com/ipcheck/getdata/', ['ip' => $ip]);
        if ($response->ok()) {
            $data = $response->json();
            if ($data) {
                if ($data['error'] === false && $data['data']['success']) {
                    $result = $data['data']['data'];

                    return [
                        'in' => [
                            'icmp' => $result['innerICMP'],
                            'tcp' => $result['innerTCP'],
                        ],
                        'out' => [
                            'icmp' => $result['outICMP'],
                            'tcp' => $result['outTCP'],
                        ],
                    ];
                }
                Log::warning('【阻断检测】检测'.$ip.'时，[vps234]接口返回'.var_export($data, true));
            }
            Log::warning("【阻断检测】检测{$ip}时, [vps234]接口返回异常");
        }

        return null;
    }

    private function flyzy2005(string $ip, int $port): ?array
    { // 开发依据: https://www.flyzy2005.cn/tech/ip-check/
        $response_inner = self::$basicRequest->get("https://mini.flyzy2005.cn/ip_check.php?ip=$ip&port=$port");
        $response_outer = self::$basicRequest->get("https://mini.flyzy2005.cn/ip_check_outside.php?ip=$ip&port=$port");

        if ($response_inner->ok() && $response_outer->ok()) {
            return $this->common_detection($response_inner->json(), $response_outer->json(), $ip);
        }

        return null;
    }

    private function idcoffer(string $ip, int $port): ?array
    { // 开发依据: https://www.idcoffer.com/ipcheck
        $response_inner = self::$basicRequest->get("https://api.24kplus.com/ipcheck?host=$ip&port=$port");
        $response_outer = self::$basicRequest->get("https://api.idcoffer.com/ipcheck?host=$ip&port=$port");

        if ($response_inner->ok() && $response_outer->ok()) {
            $inner = $response_inner->json();
            $outer = $response_outer->json();
            if ($inner && $outer) {
                if ($inner['code'] && $outer['code']) {
                    return [
                        'in' => [
                            'icmp' => $inner['data']['ping'],
                            'tcp' => $inner['data']['tcp'],
                        ],
                        'out' => [
                            'icmp' => $outer['data']['ping'],
                            'tcp' => $outer['data']['tcp'],
                        ],
                    ];
                }
                Log::warning('【阻断检测】检测'.$ip.$port.'时，[idcoffer]接口返回'.var_export($inner, true).PHP_EOL.var_export($outer, true));
            }
            Log::warning("【阻断检测】检测{$ip}时，[idcoffer]接口返回异常");
        }

        return null;
    }

    private function ip112(string $ip, int $port = 443): ?array
    { // 开发依据: https://ip112.cn/
        $response_inner = self::$basicRequest->asForm()->post('https://api.ycwxgzs.com/ipcheck/index.php', ['ip' => $ip, 'port' => $port]);
        $response_outer = self::$basicRequest->asForm()->post('https://api.52bwg.com/ipcheck/ipcheck.php', ['ip' => $ip, 'port' => $port]);

        if ($response_inner->ok() && $response_outer->ok()) {
            $inner = $response_inner->json();
            $outer = $response_outer->json();
            if ($inner && $outer) {
                return [
                    'in' => [
                        'icmp' => str_contains($inner['icmp'], 'green'),
                        'tcp' => str_contains($inner['tcp'], 'green'),
                    ],
                    'out' => [
                        'icmp' => str_contains($outer['icmp'], 'green'),
                        'tcp' => str_contains($outer['tcp'], 'green'),
                    ],
                ];
            }
        }
        Log::warning("【阻断检测】检测{$ip}时，[ip112]接口返回异常");

        return null;
    }

    private function upx8(string $ip, int $port = 443): ?array
    { // 开发依据: https://blog.upx8.com/ipcha.html
        $response_inner = self::$basicRequest->asForm()->post('https://ip.upx8.com/check.php', ['ip' => $ip, 'port' => $port]);
        $response_outer = self::$basicRequest->asForm()->post('https://ip.7761.cf/check.php', ['ip' => $ip, 'port' => $port]);

        if ($response_inner->ok() && $response_outer->ok()) {
            $inner = $response_inner->json();
            $outer = $response_outer->json();
            if ($inner && $outer) {
                return [
                    'in' => [
                        'icmp' => $inner['icmp'] === '正常',
                        'tcp' => $inner['tcp'] === '正常',
                    ],
                    'out' => [
                        'icmp' => $outer['icmp'] === '正常',
                        'tcp' => $outer['tcp'] === '正常',
                    ],
                ];
            }
        }
        Log::warning("【阻断检测】检测{$ip}时，[upx8]接口返回异常");

        return null;
    }

    private function vps1352(string $ip, int $port): ?array
    { // 开发依据: https://www.51vps.info/ipcheck.html https://www.vps1352.com/ipcheck.html 有缺陷api,查不了海外做判断 备用
        $response = self::$basicRequest->asForm()->withHeader('Referer', 'https://www.51vps.info')->post('https://www.vps1352.com/check.php', ['ip' => $ip, 'port' => $port]);

        if ($response->ok()) {
            $data = $response->json();
            if ($data) {
                return [
                    'in' => [
                        'icmp' => $data['icmp'] === '开放',
                        'tcp' => $data['tcp'] === '开放',
                    ],
                    'out' => [
                        'icmp' => true,
                        'tcp' => true,
                    ],
                ];
            }
        }
        Log::warning("【阻断检测】检测{$ip}时, [vps1352.com]接口返回异常");

        return null;
    }

    private function rss(string $ip, int $port): ?array
    { // https://ip.rss.ink/index/check
        $client = self::$basicRequest->withHeader('X-Token', '5AXfB1xVfuq5hxv4');

        foreach (['in', 'out'] as $type) {
            foreach (['icmp', 'tcp'] as $protocol) {
                $response = $client->get('https://ip.rss.ink/netcheck/'.($type === 'in' ? 'cn' : 'global')."/api/check/$protocol?ip=$ip".($protocol === 'tcp' ? "&port=$port" : ''));

                if ($response->ok()) {
                    $data = $response->json();
                    $ret[$type][$protocol] = $data['msg'] === 'success';
                }
            }
        }

        if (! isset($ret)) {
            Log::warning("【阻断检测】检测{$ip}时, [rss]接口返回异常");
        }

        return $ret ?? null;
    }
}
