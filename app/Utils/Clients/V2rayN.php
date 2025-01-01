<?php

namespace App\Utils\Clients;

use App\Models\User;
use App\Utils\Clients\Protocols\URLSchemes;
use App\Utils\Library\Templates\Client;

class V2rayN implements Client
{
    public const AGENT = ['v2rayn', 'v2rayng', 'v2rayu'];

    public function getConfig(array $servers, User $user): string
    {
        $uri = '';
        if (sysConfig('is_custom_subscribe')) {
            $text = '';
            if ($user->expiration_date > date('Y-m-d')) {
                if ($user->transfer_enable === 0) {
                    $text .= trans('user.account.remain').': 0';
                } else {
                    $text .= trans('user.account.remain').': '.$user->transfer_enable_formatted;
                }
                $text .= ', '.trans('model.user.expired_date').": $user->expiration_date";
            } else {
                $text .= trans('user.account.reason.expired');
            }
            $uri .= 'trojan://0@0.0.0.0:0?peer=0.0.0.0#'.rawurlencode($text);
        }

        return base64_encode($uri.URLSchemes::build($servers, false));
    }
}
