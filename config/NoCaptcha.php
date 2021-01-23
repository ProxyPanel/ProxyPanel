<?php

return [
    'secret' => env('NOCAPTCHA_SECRET'),
    'sitekey' => env('NOCAPTCHA_SITEKEY'),
    'server-get-config' => false,
    'options' => [
        'timeout' => 30,
    ],
];
