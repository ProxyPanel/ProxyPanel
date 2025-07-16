<?php

/*
 * Developed based on
 * https://github.com/crossutility/Quantumult/blob/master/quantumult-uri-scheme.md
 *
 */

namespace App\Utils\Clients;

use App\Models\User;
use App\Utils\Clients\Protocols\QuantumultX;
use App\Utils\Clients\Protocols\URLSchemes;
use App\Utils\Library\Templates\Client;

class Quantumult implements Client
{
    public const AGENT = ['quantumult', 'quantumult%20x'];

    public function getConfig(array $servers, User $user, string $target): string
    {
        // display remaining traffic and expire date
        if (sysConfig('is_custom_subscribe')) {
            header("Subscription-Userinfo: upload=$user->u; download=$user->d; total=$user->transfer_enable; expire=".strtotime($user->expired_at));
        }

        if (str_contains($target, 'quantumult%20x')) {
            return QuantumultX::build($servers, true);
        }

        return URLSchemes::build($servers);
    }
}
