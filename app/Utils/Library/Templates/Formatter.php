<?php

namespace App\Utils\Library\Templates;

interface Formatter
{
    public static function buildShadowsocks(array $server): array|string|null;

    public static function buildShadowsocksr(array $server): array|string|null;

    public static function buildVmess(array $server): array|string|null;

    public static function buildTrojan(array $server): array|string|null;

    public static function buildHysteria2(array $server): array|string|null;
}
