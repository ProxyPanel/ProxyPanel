<?php

declare(strict_types=1);

return [
    'forbidden' => [
        'access' => 'Unknown IP or proxy detected. Access denied!',
        'bots' => 'Bot access detected. Access denied!',
        'china' => 'China-based IP or proxy detected. Access denied!',
        'oversea' => 'Overseas IP or proxy detected. Access denied!',
        'redirect' => 'Detected (:ip :url) accessing via subscription link. Redirected.',
        'unknown' => 'Unknown interception mode. Please check system settings!',
    ],
    'get_ip' => 'Failed to retrieve IP information',
    'log' => 'Log',
    'refresh' => 'Refresh',
    'refresh_page' => 'Please refresh the page and try again',
    'report' => 'Error report:',
    'safe_code' => 'Please enter the security code',
    'safe_enter' => 'Access via Secure Entry',
    'subscribe' => [
        'banned_until' => 'Account banned until :time. Please try again after unblocking.',
        'expired' => 'Account expired. Please renew your subscription.',
        'none' => 'No available nodes',
        'out' => 'Data exhausted. Please purchase more or reset your quota.',
        'question' => 'Account issue detected. Visit the official site for details.',
        'sub_banned' => 'Subscription link has been banned. Check the official site for details.',
        'unknown' => 'Invalid subscription link. Please obtain a new one.',
        'user' => 'Invalid link. Account does not exist. Please obtain a new one.',
        'user_disabled' => 'Account has been disabled.',
    ],
    'title' => '⚠️ Error Occurred',
    'unsafe_enter' => 'Access via Unsecure Entry',
    'visit' => 'Please visit',
    'whoops' => 'Whoops!',
];
