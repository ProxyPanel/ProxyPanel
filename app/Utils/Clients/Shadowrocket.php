<?php

namespace App\Utils\Clients;

use App\Models\User;
use App\Utils\Clients\Protocols\URLSchemes;
use App\Utils\Library\Templates\Client;

class Shadowrocket implements Client
{
    public const AGENT = ['shadowrocket'];

    public function getConfig(array $servers, User $user): string
    {
        $uri = '';
        //display remaining traffic and expire date
        if (sysConfig('is_custom_subscribe')) {
            $usedTraffic = formatBytes($user->used_traffic);
            $remainTraffic = formatBytes($user->unused_traffic);
            $uri = "STATUS=📊:{$usedTraffic}💾:{$remainTraffic}📅:$user->expiration_date\r\n";
        }

        return base64_encode($uri.URLSchemes::build($servers, false));
    }
}
