<?php

namespace App\Utils\Library\Templates;

use App\Models\User;

interface Client
{
    /** 产出订阅正文；$target 是已命中 AGENT 的 UA，入口直接对它做 str_contains() */
    public function getConfig(array $servers, User $user, string $target): string;
}
