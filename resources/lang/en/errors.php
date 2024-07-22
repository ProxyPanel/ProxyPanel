<?php

declare(strict_types=1);

return [
    'forbidden' => [
        'access' => 'Detected unknown IP or proxy access, access denied',
        'bots' => 'Detected bot access, access denied',
        'china' => 'Detected China IP or proxy access, access denied',
        'oversea' => 'Detected overseas IP or proxy access, access denied',
        'unknown' => 'Unknown forbidden access mode! Please modify the [Access Restriction] in the system settings!',
        'redirect' => 'Detected (:ip :url) accessing through a subscription link, forcing a redirect.',
    ],
    'log' => 'Log',
    'refresh' => 'Refresh',
    'refresh_page' => 'Please refresh the page and try again',
    'report' => 'The error carried a report: ',
    'safe_enter' => 'Safe Entrance',
    'safe_code' => 'Please enter the safe code',
    'title' => '⚠️ Error Triggered',
    'unsafe_enter' => 'Unsafe Entrance',
    'visit' => 'Please visit',
    'whoops' => 'Whoops!',
    'get_ip' => 'Failed to retrieve IP information',
    'subscribe' => [
        'unknown' => 'Invalid subscription link! Please obtain a new one!',
        'sub_banned' => 'Subscription banned! Visit the website for details',
        'user' => 'Invalid URL, account does not exist!',
        'user_disabled' => 'Account Disabled! Contact Support!',
        'banned_until' => 'Account banned until :time, please wait for unlock!',
        'out' => 'OUT OF DATA! Please purchase more or reset data!',
        'expired' => 'Account expired! Please renew your subscription!',
        'question' => 'Account issues!? Visit the website for details',
        'none' => 'No available nodes',
    ],
];
