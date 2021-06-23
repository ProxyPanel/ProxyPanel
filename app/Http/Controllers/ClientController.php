<?php

namespace App\Http\Controllers;

use App\Components\Client\Clash;
use App\Components\Client\QuantumultX;
use App\Components\Client\Surfboard;
use App\Components\Client\Surge;
use App\Components\Client\URLSchemes;
use App\Components\Client\V2rayN;
use App\Models\User;
use File;
use Symfony\Component\Yaml\Yaml;

class ClientController extends Controller
{
    public function config(string $target, User $user, array $servers)
    {
        if (strpos($target, 'quantumult%20x') !== false) {
            return $this->quantumultX($user, $servers);
        }
        if (strpos($target, 'quantumult') !== false) {
            return $this->quantumult($user, $servers);
        }
        if (strpos($target, 'clash') !== false) {
            return $this->clash($servers);
        }
        if (strpos($target, 'surfboard') !== false) {
            return $this->surfboard($user, $servers);
        }
        if (strpos($target, 'surge') !== false) {
            return $this->surge($user, $servers);
        }
        if (strpos($target, 'shadowrocket') !== false) {
            return $this->shadowrocket($user, $servers);
        }
        if (strpos($target, 'v2rayn') !== false) {
            return $this->v2rayN($servers);
        }
        if (strpos($target, 'v2rayng') !== false) {
            return $this->v2rayN($servers);
        }
        if (strpos($target, 'v2rayu') !== false) {
            return $this->v2rayN($servers);
        }
//            if (strpos($target, 'shadowsocks') !== false) {
//                exit($this->shaodowsocksSIP008($servers));
//            }
        return $this->origin($servers);
    }

    private function quantumultX(User $user, array $servers = []): string
    {
        $uri = '';
        if (sysConfig('is_custom_subscribe')) {
            header("subscription-userinfo: upload={$user->u}; download={$user->d}; total={$user->transfer_enable}; expire={$user->expired_at}");
        }
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
        if (sysConfig('is_custom_subscribe')) {
            header("subscription-userinfo: upload={$user->u}; download={$user->d}; total={$user->transfer_enable}; expire={$user->expired_at}");
        }

        return $this->origin($servers);
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

        $config['proxies'] = array_merge($config['proxies'] ?: [], $proxy ?? []);
        foreach ($config['proxy-groups'] as $k => $v) {
            if (! is_array($config['proxy-groups'][$k]['proxies'])) {
                continue;
            }
            $config['proxy-groups'][$k]['proxies'] = array_merge($config['proxy-groups'][$k]['proxies'], $proxies ?? []);
        }

        return str_replace('$app_name', sysConfig('website_name'), Yaml::dump($config));
    }

    private function surfboard(User $user, array $servers = [])
    {
        $proxies = '';
        $proxyGroup = '';

        foreach ($servers as $server) {
            if ($server['type'] === 'shadowsocks') {
                $proxies .= Surfboard::buildShadowsocks($server);
                $proxyGroup .= $server['name'].', ';
            }
            if ($server['type'] === 'v2ray') {
                $proxies .= Surfboard::buildVmess($server);
                $proxyGroup .= $server['name'].', ';
            }
        }

        $defaultConfig = base_path().'/resources/rules/default.surfboard.conf';
        $customConfig = base_path().'/resources/rules/custom.surfboard.conf';
        if (File::exists($customConfig)) {
            $config = file_get_contents($customConfig);
        } else {
            $config = file_get_contents($defaultConfig);
        }

        // Subscription link
        $subsURL = route('sub', $user->subscribe->code);

        return str_replace(['$subs_link', '$proxies', '$proxy_group'], [$subsURL, $proxies, rtrim($proxyGroup, ', ')], $config);
    }

    private function surge(User $user, array $servers = [])
    {
        $proxies = '';
        $proxyGroup = '';

        foreach ($servers as $server) {
            if ($server['type'] === 'shadowsocks') {
                $proxies .= Surge::buildShadowsocks($server);
                $proxyGroup .= $server['name'].', ';
            }
            if ($server['type'] === 'v2ray') {
                $proxies .= Surge::buildVmess($server);
                $proxyGroup .= $server['name'].', ';
            }
            if ($server['type'] === 'trojan') {
                $proxies .= Surge::buildTrojan($server);
                $proxyGroup .= $server['name'].', ';
            }
        }

        $defaultConfig = base_path().'/resources/rules/default.surge.conf';
        $customConfig = base_path().'/resources/rules/custom.surge.conf';
        if (File::exists($customConfig)) {
            $config = file_get_contents($customConfig);
        } else {
            $config = file_get_contents($defaultConfig);
        }

        // Subscription link
        $subsURL = route('sub', $user->subscribe->code);

        return str_replace(['$subs_link', '$proxies', '$proxy_group'], [$subsURL, $proxies, rtrim($proxyGroup, ', ')], $config);
    }

    private function shadowrocket(User $user, array $servers = []): string
    {
        //display remaining traffic and expire date
        $uri = '';
        if (sysConfig('is_custom_subscribe')) {
            $upload = flowAutoShow($user->u);
            $download = flowAutoShow($user->d);
            $totalTraffic = flowAutoShow($user->transfer_enable);
            $uri = "STATUS=📤:{$upload}📥:{$download}⏳:{$totalTraffic}📅:{$user->expired_at}\r\n";
        }
        $uri .= $this->origin($servers, false);

        return base64_encode($uri);
    }

    private function shaodowsocksSIP008(array $servers = []): string
    {
        foreach ($servers as $server) {
            if ($server['type'] === 'shadowsocks') {
                $configs[] = URLSchemes::buildShadowsocksSIP008($server);
            }
        }

        return json_encode(['version' => 1, 'remark' => sysConfig('website_name'), 'servers' => $configs ?? []], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function v2rayN($servers)
    {
        $uri = '';
        foreach ($servers as $server) {
            if ($server['type'] === 'shadowsocksr') {
                $uri .= V2rayN::buildShadowsocksr($server);
            }
            if ($server['type'] === 'v2ray') {
                $uri .= V2rayN::buildVmess($server);
            }
            if ($server['type'] === 'trojan') {
                $uri .= V2rayN::buildTrojan($server);
            }
        }

        return base64_encode($uri);
    }
}
