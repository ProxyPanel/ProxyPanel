<?php

return [
    'secret' => env('HCAPTCHA_SECRET'),
    'sitekey' => env('HCAPTCHA_SITEKEY'),
    'server-get-config' => TRUE,
    'options' => [
        'timeout' => 30,
    ],
];
