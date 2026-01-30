<?php

return [
    'payment' => [
        'icon' => [
            0 => 'creditpay.svg',
            1 => 'alipay.png',
            2 => 'qq.png',
            3 => 'wechat.png',
            4 => 'coin.png',
            5 => 'paypal.png',
            6 => 'stripe.png',
            7 => 'pay.svg',
        ],
    ],

    'oauth' => [
        'labels' => [
            'facebook' => 'Facebook',
            'twitter-oauth-2' => 'Twitter',
            'linkedin-openid' => 'LinkedIn',
            'google' => 'Google',
            'github' => 'GitHub',
            'gitlab' => 'GitLab',
            'bitbucket' => 'Bitbucket',
            'slack' => 'Slack',
            'telegram' => 'Telegram',
        ],
        'icon' => [
            'facebook' => 'fa-facebook',
            'twitter-oauth-2' => 'fa-twitter',
            'linkedin-openid' => 'fa-linkedin',
            'google' => 'fa-google',
            'github' => 'fa-github',
            'gitlab' => 'fa-gitlab',
            'bitbucket' => 'fa-bitbucket',
            'slack' => 'fa-slack',
            'telegram' => 'fa-telegram',
        ],
    ],
    'notification' => [
        'labels' => [
            1 => 'email',
            2 => 'serverchan',
            3 => 'bark',
            4 => 'telegram',
            5 => 'wechat',
            6 => 'tg_chat',
            7 => 'pushplus',
            8 => 'iyuu',
            9 => 'pushdeer',
            10 => 'dingtalk',
        ],
    ],

    'language' => [
        'de' => ['Deutsch', 'de', 'de-DE'],
        'en' => ['English', 'us', 'en-US'],
        'fa' => ['فارسی', 'ir', 'fa-IR'],
        'ja' => ['日本語', 'jp', 'ja-JP'],
        'ko' => ['한국어', 'kr', 'ko-KR'],
        'ru' => ['Русский', 'ru', 'ru'],
        'vi' => ['Tiếng Việt', 'vn', 'vi-VN'],
        'zh_CN' => ['简体中文', 'cn', 'zh-CN'],
    ],

    'currency' => [
        'ca' => ['name' => 'Canadian Dollar', 'code' => 'CAD', 'symbol' => 'C$'],
        'eu' => ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€'],
        'gb' => ['name' => 'Pound Sterling', 'code' => 'GBP', 'symbol' => '£'],
        'sg' => ['name' => 'Singapore Dollar', 'code' => 'SGD', 'symbol' => 'S$'],
        'us' => ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$'],
        'cn' => ['name' => '人民币', 'code' => 'CNY', 'symbol' => '¥'],
        'tw' => ['name' => '新臺幣', 'code' => 'TWD', 'symbol' => 'NT$'],
        'jp' => ['name' => '日本円', 'code' => 'JPY', 'symbol' => '¥'],
        'hk' => ['name' => '港元', 'code' => 'HKD', 'symbol' => 'HK$'],
        'kr' => ['name' => '대한민국 원', 'code' => 'KRW', 'symbol' => '₩'],
    ],

    'contact' => [
        'telegram' => env('CONTACT_TELEGRAM', null),
        'qq' => env('CONTACT_QQ', null),
    ],

    'proxy_protocols' => [
        0 => 'Shadowsocks',
        1 => 'ShadowsocksR',
        2 => 'V2Ray',
        3 => 'Trojan',
        4 => 'VNET',
        5 => 'Hysteria2',
    ],
];
