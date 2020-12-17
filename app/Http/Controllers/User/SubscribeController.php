<?php

namespace App\Http\Controllers\User;

use App\Components\Client\Clash;
use App\Components\Client\QuantumultX;
use App\Components\Client\Shadowrocket;
use App\Components\Client\Surfboard;
use App\Components\Client\Surge;
use App\Components\Client\URLSchemes;
use App\Components\IP;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscribe;
use App\Models\UserSubscribeLog;
use Arr;
use File;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Symfony\Component\Yaml\Yaml;

class SubscribeController extends Controller
{
    private $subType;

    // 通过订阅码获取订阅信息
    public function getSubscribeByCode(Request $request, string $code)
    {
        if (empty($code)) {
            return Redirect::route('login');
        }
        $this->subType = $request->input('type');
        $target = strtolower($request->input('target') ?? (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));

        // 检查订阅码是否有效
        $subscribe = UserSubscribe::whereCode($code)->first();
        if (! $subscribe) {
            return $this->failed('使用链接错误！请重新获取！');
        }

        if ($subscribe->status !== 1) {
            return $this->failed('链接已被封禁，请前往官网查询原因！');
        }

        // 检查用户是否有效
        $user = $subscribe->user;
        if (! $user) {
            return $this->failed('错误链接，账号不存在！请重新获取链接');
        }

        if ($user->status === -1) {
            return $this->failed('账号被禁用!');
        }

        if ($user->enable !== 1) {
            if ($user->ban_time) {
                return $this->failed('账号封禁至'.$user->ban_time.',请解封后再更新！');
            }

            $unusedTraffic = $user->transfer_enable - $user->usedTraffic();
            if ($unusedTraffic <= 0) {
                return $this->failed('流量耗尽！请重新购买或重置流量！');
            }

            if ($user->expired_at < date('Y-m-d')) {
                return $this->failed('账号过期！请续费！');
            }

            return $this->failed('账号存在问题，请前往官网查询！');
        }

        // 更新访问次数
        $subscribe->increment('times', 1);

        // 记录每次请求
        $this->subscribeLog($subscribe->id, IP::getClientIp(), $request->headers);

        // 获取这个账号可用节点
        $query = $user->whereIsSubscribe(1)->userAccessNodes();

        if ($this->subType === 1) {
            $query = $query->whereIn('type', [1, 4]);
        } elseif ($this->subType) {
            $query = $query->whereType($this->subType);
        }

        $nodeList = $query->orderByDesc('sort')->orderBy('id')->get();
        if (empty($nodeList)) {
            return $this->failed('无可用节点');
        }

        $servers = [];
        foreach ($nodeList as $node) {
            $servers[] = $node->config($user);
        }

        // 打乱数组
        if (sysConfig('rand_subscribe')) {
            $servers = Arr::shuffle($servers);
        }

        if (sysConfig('subscribe_max')) {
            $servers = array_slice($servers, 0, (int) sysConfig('subscribe_max'));
        }

        if ($target) {
            if (strpos($target, 'quantumult x') !== false) {
                exit($this->quantumultX($user, $servers));
            }
            if (strpos($target, 'quantumult') !== false) {
                exit($this->quantumult($user, $servers));
            }
            if (strpos($target, 'clash') !== false) {
                exit($this->clash($servers));
            }
            if (strpos($target, 'surfboard') !== false) {
                exit($this->surfboard($user, $servers));
            }
            if (strpos($target, 'surge') !== false) {
                exit($this->surge($user, $servers));
            }
            if (strpos($target, 'shadowrocket') !== false) {
                exit($this->shadowrocket($user, $servers));
            }
            if (strpos($target, 'shadowsocks') !== false) {
                exit($this->shaodowsocksSIP008($servers));
            }
        }
        exit($this->origin($servers));
    }

    // TODO 通过Token获取订阅信息

    // 抛出错误的节点信息，用于兼容防止客户端订阅失败
    private function failed($text)
    {
        return Response::make(base64url_encode($this->infoGenerator($text)), 200);
    }

    private function infoGenerator($text): string
    {
        $result = null;
        switch ($this->subType) {
            case 2:
                $result = 'vmess://'.base64url_encode(json_encode([
                    'v' => '2', 'ps' => $text, 'add' => '0.0.0.0', 'port' => 0, 'id' => 0, 'aid' => 0, 'net' => 'tcp',
                    'type' => 'none', 'host' => '', 'path' => '/', 'tls' => 'tls',
                ], JSON_PRETTY_PRINT));
                break;
            case 3:
                $result = 'trojan://0@0.0.0.0:0?peer=0.0.0.0#'.rawurlencode($text);
                break;
            case 1:
            case 4:
            default:
                $result = 'ssr://'.base64url_encode('0.0.0.0:0:origin:none:plain:'.base64url_encode('0000').'/?obfsparam=&protoparam=&remarks='.base64url_encode($text).'&group='.base64url_encode(sysConfig('website_name')).'&udpport=0&uot=0');
                break;
        }

        return $result.PHP_EOL;
    }

    // 写入订阅访问日志
    private function subscribeLog($subscribeId, $ip, $headers): void
    {
        $log = new UserSubscribeLog();
        $log->user_subscribe_id = $subscribeId;
        $log->request_ip = $ip;
        $log->request_time = date('Y-m-d H:i:s');
        $log->request_header = $headers;
        $log->save();
    }

    private function quantumultX(User $user, array $servers = []): string
    {
        $uri = '';
        header("subscription-userinfo: upload={$user->u}; download={$user->d}; total={$user->transfer_enable}; expire={$user->expired_at}");
        foreach ($servers as $server) {
            if ($server['type'] === 'shadowsocks') {
                $uri .= QuantumultX::buildShadowsocks($server);
            }
            if ($server['type'] === 'shadowsocksr') {
                $uri .= QuantumultX::buildShadowsocksr($server);
            }
            if ($server['type'] === 'v2ray') {
                $uri .= QuantumultX::buildVmess($server);
            }
            if ($server['type'] === 'trojan') {
                $uri .= QuantumultX::buildTrojan($server);
            }
        }

        return base64_encode($uri);
    }

    private function quantumult(User $user, array $servers = []): string
    {
        header('subscription-userinfo: upload='.$user->u.'; download='.$user->d.';total='.$user->transfer_enable).'; expire='.strtotime($user->expired_at);
        $uri = $this->origin($servers);

        return base64_encode($uri);
    }

    private function origin(array $servers = [], bool $encode = true): string
    {
        $uri = '';
        foreach ($servers as $server) {
            if ($server['type'] === 'shadowsocks') {
                $uri .= URLSchemes::buildShadowsocks($server);
            }
            if ($server['type'] === 'shadowsocksr') {
                $uri .= URLSchemes::buildShadowsocksr($server);
            }
            if ($server['type'] === 'v2ray') {
                $uri .= URLSchemes::buildVmess($server);
            }
            if ($server['type'] === 'trojan') {
                $uri .= URLSchemes::buildTrojan($server);
            }
        }

        return $encode ? base64_encode($uri) : $uri;
    }

    private function clash($servers)
    {
        $defaultConfig = base_path().'/resources/rules/default.clash.yaml';
        $customConfig = base_path().'/resources/rules/custom.clash.yaml';
        if (File::exists($customConfig)) {
            $config = Yaml::parseFile($customConfig);
        } else {
            $config = Yaml::parseFile($defaultConfig);
        }
        $proxy = [];
        $proxies = [];

        foreach ($servers as $server) {
            if ($server['type'] === 'shadowsocks') {
                $proxy[] = Clash::buildShadowsocks($server);
                $proxies[] = $server['name'];
            }
            if ($server['type'] === 'shadowsocksr') {
                $proxy[] = Clash::buildShadowsocksr($server);
                $proxies[] = $server['name'];
            }
            if ($server['type'] === 'v2ray') {
                $proxy[] = Clash::buildVmess($server);
                $proxies[] = $server['name'];
            }
            if ($server['type'] === 'trojan') {
                $proxy[] = Clash::buildTrojan($server);
                $proxies[] = $server['name'];
            }
        }

        $config['proxies'] = array_merge($config['proxies'] ?: [], $proxy);
        foreach ($config['proxy-groups'] as $k => $v) {
            if (! is_array($config['proxy-groups'][$k]['proxies'])) {
                continue;
            }
            $config['proxy-groups'][$k]['proxies'] = array_merge($config['proxy-groups'][$k]['proxies'], $proxies);
        }
        $yaml = Yaml::dump($config);
        $yaml = str_replace('$app_name', sysConfig('website_name'), $yaml);

        return $yaml;
    }

    private function surfboard(User $user, array $servers = [])
    {
        $proxies = '';
        $proxyGroup = '';

        foreach ($servers as $server) {
            if ($server['type'] === 'shadowsocks') {
                // [Proxy]
                $proxies .= Surfboard::buildShadowsocks($server);
                // [Proxy Group]
                $proxyGroup .= $server['name'].', ';
            }
            if ($server['type'] === 'v2ray') {
                // [Proxy]
                $proxies .= Surfboard::buildVmess($server);
                // [Proxy Group]
                $proxyGroup .= $server['name'].', ';
            }
        }

        $defaultConfig = base_path().'/resources/rules/default.surfboard.conf';
        $customConfig = base_path().'/resources/rules/custom.surfboard.conf';
        if (File::exists($customConfig)) {
            $config = file_get_contents("$customConfig");
        } else {
            $config = file_get_contents("$defaultConfig");
        }

        // Subscription link
        $subsURL = route('sub', $user->subscribe->code);

        $config = str_replace('$subs_link', $subsURL, $config);
        $config = str_replace('$proxies', $proxies, $config);
        $config = str_replace('$proxy_group', rtrim($proxyGroup, ', '), $config);

        return $config;
    }

    private function surge(User $user, array $servers = [])
    {
        $proxies = '';
        $proxyGroup = '';

        foreach ($servers as $server) {
            if ($server['type'] === 'shadowsocks') {
                // [Proxy]
                $proxies .= Surge::buildShadowsocks($server);
                // [Proxy Group]
                $proxyGroup .= $server['name'].', ';
            }
            if ($server['type'] === 'v2ray') {
                // [Proxy]
                $proxies .= Surge::buildVmess($server);
                // [Proxy Group]
                $proxyGroup .= $server['name'].', ';
            }
            if ($server['type'] === 'trojan') {
                // [Proxy]
                $proxies .= Surge::buildTrojan($server);
                // [Proxy Group]
                $proxyGroup .= $server['name'].', ';
            }
        }

        $defaultConfig = base_path().'/resources/rules/default.surge.conf';
        $customConfig = base_path().'/resources/rules/custom.surge.conf';
        if (File::exists($customConfig)) {
            $config = file_get_contents("$customConfig");
        } else {
            $config = file_get_contents("$defaultConfig");
        }

        // Subscription link
        $subsURL = route('sub', $user->subscribe->code);

        $config = str_replace('$subs_link', $subsURL, $config);
        $config = str_replace('$proxies', $proxies, $config);
        $config = str_replace('$proxy_group', rtrim($proxyGroup, ', '), $config);

        return $config;
    }

    private function shadowrocket(User $user, array $servers = []): string
    {
        //display remaining traffic and expire date
        $upload = flowAutoShow($user->u);
        $download = flowAutoShow($user->d);
        $totalTraffic = flowAutoShow($user->transfer_enable);
        $uri = "STATUS=🚀↑:{$upload},↓:{$download},TOT:{$totalTraffic}💡Expires:{$user->expired_at}\r\n";
        $uri .= $this->origin($servers, false);

        return base64_encode($uri);
    }

    private function shaodowsocksSIP008(array $servers = []): string
    {
        $configs = [];
        $subs = [];
        $subs['servers'] = [];

        foreach ($servers as $server) {
            if ($server['type'] === 'shadowsocks') {
                $configs[] = URLSchemes::buildShadowsocksSIP008($server);
            }
        }

        $subs['version'] = 1;
        $subs['remark'] = sysConfig('website_name');
        $subs['servers'] = array_merge($subs['servers'] ?: [], $configs);

        return json_encode($subs, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
