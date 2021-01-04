<?php

namespace App\Components;

use Http;
use Log;

class NetworkDetection
{
    /**
     * 用外部API进行Ping检测.
     *
     * @param  string  $ip  被检测的IP或者域名
     *
     * @return bool|array
     */
    public function ping(string $ip)
    {
        $round = 0;
        // 依次尝试接口
        while (true) {
            switch ($round) {
                case 0:
                    $ret = $this->oiowebPing($ip);
                    break;
                default:
                    return false;
            }
            if ($ret !== false) {
                return $ret;
            }
            $round++;
        }
    }

    private function oiowebPing(string $ip)
    {
        $msg = null;
        foreach ([1, 6, 14] as $line) {
            $url = "https://api.oioweb.cn/api/hostping.php?host={$ip}&node={$line}"; // https://api.iiwl.cc/api/ping.php?host=
            $response = Http::timeout(15)->get($url);

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

    /**
     * 通过众多API进行节点阻断检测.
     *
     * @param  string  $ip  被检测的IP
     * @param  bool  $is_icmp  TRUE 为ICMP,FALSE 为tcp
     * @param  int|null  $port  检测端口，默认为空
     *
     * @return bool|string
     */
    public function networkCheck(string $ip, bool $is_icmp, int $port = null)
    {
        $round = 0;
        // 依次尝试接口
        while (true) {
            switch ($round) {
                case 0:
                    $ret = $this->idcWiki($ip, $is_icmp, $port);
                    break;
                case 1:
                    $ret = $this->flyzy2005($ip, $is_icmp, $port);
                    break;
                case 2:
                    $ret = $this->vps234($ip, $is_icmp);
                    break;
                case 3:
                    $ret = $this->vpsaff($ip, $is_icmp, $port);
                    break;
                default:
                    return false;
            }
            if ($ret !== false) {
                return $ret;
            }
            $round++;
        }
    }

    private function idcWiki(string $ip, bool $is_icmp, int $port = null)
    {
        if ($is_icmp) {
            $type_string = 'icmp/';
            $checkName = 'ICMP';
        } else {
            $type_string = 'tcp_ack/';
            $checkName = 'TCP';
        }
        if ($port) {
            $port = '/'.$port;
            $type_string = 'tcp_port/';
        }

        $url = "https://api.50network.com/china-firewall/check/ip/{$type_string}{$ip}{$port}";

        $response = Http::timeout(15)->get($url);

        if ($response->ok()) {
            $message = $response->json();
            if (! $message) {
                Log::warning('【'.$checkName.'阻断检测】检测'.$ip.'时，接口返回异常访问链接：'.$url);

                return false;
            }

            if (! $message['success']) {
                if ($message['error'] && $message['error'] === 'execute timeout (3s)') {
                    return false;
                }

                Log::warning('【'.$checkName.'阻断检测】检测'.$ip.$port.'时，返回'.var_export($message, true));

                return false;
            }

            if ($message['firewall-enable'] && $message['firewall-disable']) {
                return '通讯正常'; // 正常
            }

            if ($message['firewall-enable'] && ! $message['firewall-disable']) {
                return '海外阻断'; // 国外访问异常
            }

            if (! $message['firewall-enable'] && $message['firewall-disable']) {
                return '国内阻断'; // 被墙
            }

            return '机器宕机'; // 服务器宕机
        }

        return false;
    }

    private function flyzy2005(string $ip, bool $is_icmp, int $port = null)
    {
        $cn = "https://mini.flyzy2005.cn/ip_check.php?ip={$ip}&port={$port}";
        $us = "https://mini.flyzy2005.cn/ip_check_outside.php?ip={$ip}&port={$port}";

        $checkName = $is_icmp ? 'icmp' : 'tcp';

        $response_cn = Http::timeout(15)->get($cn);
        $response_us = Http::timeout(15)->get($us);

        if ($response_cn->ok() && $response_us->ok()) {
            $cn = $response_cn->json();
            $us = $response_us->json();
            if (! $cn || ! $us) {
                Log::warning("【{$checkName}阻断检测】检测{$ip}时，接口返回异常访问链接：{$cn} | {$us}");

                return false;
            }

            if ($cn[$checkName] === 'success' && $us['outside_'.$checkName] === 'success') {
                return '通讯正常'; // 正常
            }

            if ($cn[$checkName] === 'success' && $us['outside_'.$checkName] !== 'success') {
                return '海外阻断'; // 国外访问异常
            }

            if ($cn[$checkName] !== 'success' && $us['outside_'.$checkName] === 'success') {
                return '国内阻断'; // 被墙
            }

            return '机器宕机'; // 服务器宕机
        }

        return false;
    }

    private function vps234(string $ip, bool $is_icmp)
    {
        $url = 'https://www.vps234.com/ipcheck/getdata/';

        $checkName = $is_icmp ? 'ICMP' : 'TCP';

        $response = Http::timeout(15)->withoutVerifying()->asForm()->post($url, ['ip' => $ip]);
        if ($response->ok()) {
            $message = $response->json();
            if (! $message) {
                Log::warning('【'.$checkName.'阻断检测】检测'.$ip.'时，接口返回异常访问链接：'.$url);

                return false;
            }

            if (! $message['data']['success']) {
                Log::warning('【'.$checkName.'阻断检测】检测'.$ip.'时，返回'.var_export($message, true));

                return false;
            }

            if ($message['data']['data']['inner'.$checkName] && $message['data']['data']['out'.$checkName]) {
                return '通讯正常'; // 正常
            }

            if ($message['data']['data']['inner'.$checkName] && ! $message['data']['data']['out'.$checkName]) {
                return '海外阻断'; // 国外访问异常
            }

            if (! $message['data']['data']['inner'.$checkName] && $message['data']['data']['out'.$checkName]) {
                return '国内阻断'; // 被墙
            }

            return '机器宕机'; // 服务器宕机
        }

        return false;
    }

    private function vpsaff(string $ip, bool $is_icmp, int $port = null)
    {
        $cn = "https://api.24kplus.com/ipcheck?host={$ip}&port={$port}";
        $us = "https://api.vpsaff.net/ipcheck?host={$ip}&port={$port}";
        $checkName = $is_icmp ? 'ping' : 'tcp';

        $response_cn = Http::timeout(15)->get($cn);
        $response_us = Http::timeout(15)->get($us);

        if ($response_cn->ok() && $response_us->ok()) {
            $cn = $response_cn->json();
            $us = $response_us->json();
            if (! $cn || ! $us) {
                Log::warning("【{$checkName}阻断检测】检测{$ip}时，接口返回异常访问链接：{$cn} | {$us}");

                return false;
            }

            if (! $cn['code'] || ! $us['code']) {
                Log::warning('【'.$checkName.'阻断检测】检测'.$ip.$port.'时，返回'.var_export($cn, true).var_export($us, true));

                return false;
            }

            if ($cn['data'][$checkName] && $us['data'][$checkName]) {
                return '通讯正常'; // 正常
            }

            if ($cn['data'][$checkName] && ! $us['data'][$checkName]) {
                return '海外阻断'; // 国外访问异常
            }

            if (! $cn['data'][$checkName] && $us['data'][$checkName]) {
                return '国内阻断'; // 被墙
            }

            return '机器宕机'; // 服务器宕机
        }

        return false;
    }
}
