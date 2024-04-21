<?php

namespace App\Utils\Library\Templates;

interface DNS
{
    public function store(string $ip, string $type): bool;

    public function update(string $latest_ip, string $original_ip, string $type): bool;

    public function destroy(string $type, string $ip): int|bool;
}
