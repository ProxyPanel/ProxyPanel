<?php

namespace App\Utils;

use Exception;
use Http;
use Illuminate\Http\Client\PendingRequest;
use Log;

class NetworkDetection
{
    private static PendingRequest $basicRequest;

    public function ping(string $ip): ?string
    { // 用外部API进行Ping检测. TODO: 无权威外部API，功能缺失
        $testers = ['oiowebPing', 'xiaoapiPing', 'yum6Ping'];
        self::$basicRequest = Http::timeout(20)->withOptions(['http_errors' => false])->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');

        foreach ($testers as $tester) {
            try {
                $result = $this->callLatencyTester($tester, $ip);
                if ($result !== null) {
                    return $result;
                }
            } catch (Exception $e) {
                Log::error("[$tester] 网络延迟测试报错: ".$e->getMessage());

                continue;
            }
        }

        return null;
    }

    private function callLatencyTester(string $tester, string $ip): ?array
    {
        return match ($tester) {
            'oiowebPing' => $this->oiowebPing($ip),
            'xiaoapiPing' => $this->xiaoapiPing($ip),
            'yum6Ping' => $this->yum6Ping($ip),
        };
    }

    private function oiowebPing(string $ip)
    {
        $msg = null;
        foreach ([1, 6, 14] as $line) {
            $response = self::$basicRequest->get("https://api.oioweb.cn/api/hostping.php?host=$ip&node=$line"); // https://api.iiwl.cc/api/ping.php?host=

            // 发送成功
            if ($response->ok()) {
                $message = $response->json();
                if ($message && $message['code']) {
                    $msg .= "{$message['node']}：{$message['data']['Time']}<br>";
                }
            } else {
                return false;
            }
        }

        if ($msg) {
            return $msg;
        }
        Log::warning('【PING】检测'.$ip.'时，api.oioweb.cn无结果');

        // 发送错误
        return false;
    }

    private function xiaoapiPing(string $ip)
    { // 开发依据 https://xiaoapi.cn/?action=doc&id=3
        $response = self::$basicRequest->get("https://xiaoapi.cn/API/sping.php?url=$ip");

        // 发送成功
        if ($response->ok()) {
            return $response->body();
        }
        Log::warning("【PING】检测{$ip}时，xiaoapi.cn无结果");

        // 发送错误
        return false;
    }

    private function yum6Ping(string $ip)
    { // 来源 https://api.yum6.cn/ping.php?host=api.yum6.cn
        $response = self::$basicRequest->get("https://api.yum6.cn/ping.php?host=$ip");

        // 发送成功
        if ($response->ok()) {
            $msg = $response->json();
            if ($msg && $msg['state'] === '1000') {
                return "<h4>{$msg['ip']}</h4>线路【{$msg['node']}】<br> 最小值：{$msg['ping_time_min']}<br> 平均值：{$msg['ping_time_avg']}<br> 最大值：{$msg['ping_time_max']}";
            }
        }
        Log::warning('【PING】检测'.$ip.'时，api.yum6.cn无结果');

        return false; // 发送错误
    }

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
        $checkers = ['toolsdaquan', 'flyzy2005', 'idcoffer', 'ip112', 'upx8', 'vps234', 'rss', 'gd', 'vps1352'];
        self::$basicRequest = Http::timeout(15)->retry(2)->withOptions(['http_errors' => false])->withoutVerifying()->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');

        foreach ($checkers as $checker) {
            try {
                $result = $this->callChecker($checker, $ip, $port);
                if ($result !== null) {
                    return $result;
                }
            } catch (Exception $e) {
                Log::error("[$checker] 网络阻断测试报错: ".$e->getMessage());

                continue;
            }
        }

        return null;
    }

    private function callChecker(string $checker, string $ip, int $port): ?array
    {
        return match ($checker) {
            'toolsdaquan' => $this->toolsdaquan($ip, $port),
            'gd' => $this->gd($ip, $port),
            'vps234' => $this->vps234($ip),
            'flyzy2005' => $this->flyzy2005($ip, $port),
            'idcoffer' => $this->idcoffer($ip, $port),
            'ip112' => $this->ip112($ip, $port),
            'upx8' => $this->upx8($ip, $port),
            'vps1352' => $this->vps1352($ip, $port),
            'rss' => $this->rss($ip, $port),
        };
    }

    private function toolsdaquan(string $ip, int $port): ?array
    { // 开发依据: https://www.toolsdaquan.com/ipcheck/
        $response_inner = self::$basicRequest->withHeaders(['Referer' => 'https://www.toolsdaquan.com/ipcheck/'])->get("https://www.toolsdaquan.com/toolapi/public/ipchecking/$ip/$port");
        $response_outer = self::$basicRequest->withHeaders(['Referer' => 'https://www.toolsdaquan.com/ipcheck/'])->get("https://www.toolsdaquan.com/toolapi/public/ipchecking2/$ip/$port");

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
        $response = self::$basicRequest->asForm()->withHeaders(['Referer' => 'https://www.51vps.info'])->post('https://www.vps1352.com/check.php', ['ip' => $ip, 'port' => $port]);

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
        $client = self::$basicRequest->withHeaders(['X-Token' => '5AXfB1xVfuq5hxv4']);

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
