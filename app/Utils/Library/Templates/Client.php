<?php

namespace App\Utils\Library\Templates;

interface Client
{
    public static function buildShadowsocks(array $server): array|string;

    public static function buildShadowsocksr(array $server): array|string;

    public static function buildVmess(array $server): array|string;

    public static function buildTrojan(array $server): array|string;
}
