<?php

/*
 * Developed based on
 * https://clash.wiki/configuration/configuration-reference.html
 * https://docs.gtk.pw/contents/urlscheme.html#%E4%B8%8B%E8%BD%BD%E9%85%8D%E7%BD%AE
 */

namespace App\Utils\Clients;

use App\Models\User;
use App\Utils\Library\Templates\Client;
use File;
use Symfony\Component\Yaml\Yaml;

class Clash implements Client
{
    public const AGENT = ['clash', 'stash', 'bob_vpn'];

    public function getConfig(array $servers, User $user, string $target): array|string
    {
        $custom_path = '/resources/rules/custom.clash.yaml';
        if (str_contains($target, 'bob_vpn')) {
            $file_path = '/resources/rules/bob.clash.yaml';
        } elseif (File::exists(base_path().$custom_path)) {
            $file_path = $custom_path;
        } else {
            $file_path = '/resources/rules/default.clash.yaml';
        }

        $appName = sysConfig('website_name');
        header("content-disposition:attachment;filename*=UTF-8''".rawurlencode($appName).'.yaml');
        header('profile-update-interval: 24');
        header('profile-web-page-url:'.sysConfig('website_url'));
        if (sysConfig('is_custom_subscribe')) {
            // display remaining traffic and expire date
            header("subscription-userinfo: upload=$user->u; download=$user->d; total=$user->transfer_enable; expire=".strtotime($user->expired_at));
        }

        $config = Yaml::parseFile(base_path().$file_path);

        // 按照核心区分配置
        if (str_contains($target, 'clashforwindows') || str_contains($target, 'clashforandroid') || str_contains($target, 'clashx')) {
            $proxyProfiles = Formatters\Clash::build($servers);
        } elseif (str_contains($target, 'stash')) {
            $proxyProfiles = Formatters\Stash::build($servers);
        } else {
            $proxyProfiles = Formatters\ClashMeta::build($servers);
        }

        $config['proxies'] = array_merge($config['proxies'] ?: [], $proxyProfiles['proxies']);
        $names = array_column($config['proxies'], 'name');
        foreach ($config['proxy-groups'] as $k => $v) {
            if (! is_array($config['proxy-groups'][$k]['proxies'])) {
                continue;
            }
            $config['proxy-groups'][$k]['proxies'] = array_merge($config['proxy-groups'][$k]['proxies'], $names);
        }

        array_unshift($config['rules'], 'DOMAIN,'.$_SERVER['HTTP_HOST'].',DIRECT'); // Set current sub-domain to be direct

        return str_replace('$app_name', $appName, Yaml::dump($config, 2, 4, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE));
    }
}
