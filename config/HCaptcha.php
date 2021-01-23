<?php

return [
    'secret' => env('HCAPTCHA_SECRET'),
    'sitekey' => env('HCAPTCHA_SITEKEY'),
    'server-get-config' => false,
    'options' => [
        'timeout' => 30,
    ],
];
