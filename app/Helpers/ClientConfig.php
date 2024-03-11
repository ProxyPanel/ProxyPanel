<?php

namespace App\Helpers;

use App\Utils\Clients\Clash;
use App\Utils\Clients\QuantumultX;
use App\Utils\Clients\Surge;
use App\Utils\Clients\URLSchemes;
use File;
use Symfony\Component\Yaml\Yaml;

trait ClientConfig
{
    private function clientConfig(string $target): string
    {
        if (str_contains($target, 'quantumult%20x')) {
            return $this->quantumultX();
        }
        if (str_contains($target, 'quantumult')) {
            return $this->quantumult();
        }
        if (str_contains($target, 'clash') || str_contains($target, 'stash')) {
            return $this->clash();
        }
        if (str_contains($target, 'bob_vpn')) {
            return $this->clash('bob');
        }
        if (str_contains($target, 'surfboard')) {
            return $this->surfboard();
        }
        if (str_contains($target, 'surge')) {
            return $this->surge($target);
        }
        if (str_contains($target, 'shadowrocket')) {
            return $this->shadowrocket();
        }
        if (str_contains($target, 'v2rayn') || str_contains($target, 'v2rayng') || str_contains($target, 'v2rayu')) {
            return $this->v2rayN();
        }

        //        if (str_contains($target, 'shadowsocks')) {
        //            exit($this->shaodowsocksSIP008());
        //        }
        return $this->origin();
    }

    private function quantumultX(): string
    {
        $user = $this->getUser();
        $uri = '';
        if (sysConfig('is_custom_subscribe')) {
            header("subscription-userinfo: upload=$user->u; download=$user->d; total=$user->transfer_enable; expire=".strtotime($user->expired_at));
        }
        foreach ($this->getServers() as $server) {
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

    private function quantumult(): string
    {
        $user = $this->getUser();
        if (sysConfig('is_custom_subscribe')) {
            header("subscription-userinfo: upload=$user->u; download=$user->d; total=$user->transfer_enable; expire=".strtotime($user->expired_at));
        }

        return $this->origin();
    }

    private function origin(bool $encode = true): string
    {
        $uri = '';
        foreach ($this->getServers() as $server) {
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

    private function clash(?string $client = null): string
    {
        $user = $this->getUser();
        $webName = sysConfig('website_name');
        header("content-disposition:attachment;filename*=UTF-8''".rawurlencode($webName).'.yaml');
        header('profile-update-interval: 24');
        header('profile-web-page-url:'.sysConfig('website_url'));
        if (sysConfig('is_custom_subscribe')) {
            header("subscription-userinfo: upload=$user->u; download=$user->d; total=$user->transfer_enable; expire=".strtotime($user->expired_at));
        }
        $custom_path = '/resources/rules/custom.clash.yaml';
        if ($client === 'bob') {
            $file_path = '/resources/rules/bob.clash.yaml';
        } elseif (File::exists(base_path().$custom_path)) {
            $file_path = $custom_path;
        } else {
            $file_path = '/resources/rules/default.clash.yaml';
        }

        $config = Yaml::parseFile(base_path().$file_path);

        foreach ($this->getServers() as $server) {
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

        array_unshift($config['rules'], 'DOMAIN,'.$_SERVER['HTTP_HOST'].',DIRECT'); // Set current sub-domain to be direct

        return str_replace('$app_name', $webName, Yaml::dump($config, 2, 4, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE));
    }

    private function surfboard(): string
    {
        $defaultConfig = base_path().'/resources/rules/default.surfboard.conf';
        $customConfig = base_path().'/resources/rules/custom.surfboard.conf';

        return $this->sugerLike($customConfig, $defaultConfig);
    }

    private function sugerLike(string $customConfig, string $defaultConfig, string $target = ''): string
    {
        if (File::exists($customConfig)) {
            $config = file_get_contents($customConfig);
        } else {
            $config = file_get_contents($defaultConfig);
        }

        $proxies = '';
        $proxyGroup = '';
        $user = $this->getUser();
        $webName = sysConfig('website_name');
        header("content-disposition:attachment;filename*=UTF-8''".rawurlencode($webName).'.conf');
        foreach ($this->getServers() as $server) {
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

        if (str_contains($target, 'list')) {
            return $proxies;
        }

        if (sysConfig('is_custom_subscribe')) {
            $upload = formatBytes($user->u);
            $download = formatBytes($user->d);
            $totalTraffic = formatBytes($user->transfer_enable);
            $subscribeInfo = "title=$webName".trans('user.subscribe.info.title').', content='.trans('user.subscribe.info.upload').": $upload\\n".trans('user.subscribe.info.download').": $download\\n".trans('user.subscribe.info.total').": $totalTraffic\\n".trans('model.user.expired_date').": $user->expired_at";
            $config = str_replace('$subscribe_info', $subscribeInfo, $config);
        }

        return str_replace(['$subs_link', '$subs_domain', '$proxies', '$proxy_group'],
            [route('sub', $user->subscribe->code), $_SERVER['HTTP_HOST'], $proxies, rtrim($proxyGroup, ', ')],
            $config);
    }

    private function surge(string $target): string
    {
        $defaultConfig = base_path().'/resources/rules/default.surge.conf';
        $customConfig = base_path().'/resources/rules/custom.surge.conf';

        return $this->sugerLike($customConfig, $defaultConfig, $target);
    }

    private function shadowrocket(): string
    {
        //display remaining traffic and expire date
        $uri = '';
        $user = $this->getUser();
        if (sysConfig('is_custom_subscribe')) {
            $upload = formatBytes($user->u);
            $download = formatBytes($user->d);
            $totalTraffic = formatBytes($user->transfer_enable);
            $uri = "STATUS=📤:{$upload}📥:{$download}⏳:{$totalTraffic}📅:$user->expiration_date\r\n";
        }
        $uri .= $this->origin(false);

        return base64_encode($uri);
    }

    private function v2rayN(): string
    {
        $uri = '';
        $user = $this->getUser();
        if (sysConfig('is_custom_subscribe')) {
            $text = '';
            if ($user->expiration_date > date('Y-m-d')) {
                if ($user->transfer_enable === 0) {
                    $text .= trans('user.account.remain').': 0';
                } else {
                    $text .= trans('user.account.remain').': '.formatBytes($user->transfer_enable);
                }
                $text .= ', '.trans('model.user.expired_date').": $user->expiration_date";
            } else {
                $text .= trans('user.account.reason.expired');
            }
            $uri .= $this->failedProxyReturn($text, 2);
        }

        return base64_encode($uri.$this->origin(false));
    }

    private function shaodowsocksSIP008(): string
    {
        foreach ($this->getServers() as $server) {
            if ($server['type'] === 'shadowsocks') {
                $configs[] = URLSchemes::buildShadowsocksSIP008($server);
            }
        }

        return json_encode(['version' => 1, 'remark' => sysConfig('website_name'), 'servers' => $configs ?? []], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
