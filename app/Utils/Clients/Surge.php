<?php

/*
 * Developed based on
 * https://wiki.surge.community/modules
 * https://getsurfboard.com/docs/profile-format/overview/
 */

namespace App\Utils\Clients;

use App\Models\User;
use App\Utils\Library\Templates\Client;
use File;

class Surge implements Client
{
    public const AGENT = ['surge', 'surfboard'];

    public function getConfig(array $servers, User $user, string $target): string
    {
        if (str_contains($target, 'surge')) {
            $customConfig = base_path().'/resources/rules/custom.surge.conf';

            if (File::exists($customConfig)) {
                $config = file_get_contents($customConfig);
            } else {
                $config = file_get_contents(base_path().'/resources/rules/default.surge.conf');
            }
        } else {
            $customConfig = base_path().'/resources/rules/custom.surfboard.conf';

            if (File::exists($customConfig)) {
                $config = file_get_contents($customConfig);
            } else {
                $config = file_get_contents(base_path().'/resources/rules/default.surfboard.conf');
            }
        }

        $webName = sysConfig('website_name');
        header("content-disposition:attachment;filename*=UTF-8''".rawurlencode($webName).'.conf');
        $proxyProfiles = Formatters\Surge::build($servers);

        if (sysConfig('is_custom_subscribe')) {
            $upload = formatBytes($user->u);
            $download = formatBytes($user->d);
            $totalTraffic = $user->transfer_enable_formatted;
            $style = 'info';
            $remainTraffic = $user->unused_traffic;
            $remainDates = now()->diffInDays($user->expired_at, false);
            if ($remainTraffic <= 0 || $remainDates <= 0) {
                $style = 'error';
            } elseif ($remainTraffic / $user->transfer_enable <= 0.05 || $remainDates <= 7) {
                $style = 'alert';
            }

            $subscribeInfo = "title=$webName".trans('user.subscribe.info.title').', content='.trans('user.subscribe.info.upload').": $upload\n".trans('user.subscribe.info.download').": $download\n".trans('user.subscribe.info.total').": $totalTraffic\n".trans('model.user.expired_date').": $user->expired_at, style=$style";
        } else {
            $subscribeInfo = "title=$webName, content=";
        }

        return str_replace(['$subscribe_info', '$subs_link', '$subs_domain', '$proxies', '$proxy_group'], [$subscribeInfo, $user->sub_url, $_SERVER['HTTP_HOST'], $proxyProfiles['proxies'], $proxyProfiles['name']],
            $config);
    }
}
