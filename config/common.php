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

    'network_status' => [
        1 => '✔️正 常',
        2 => '🛑 海外阻断',
        3 => '🛑 国内阻断',
        4 => '❌ 断 连',
    ],

    'notification' => [
        'labels' => [
            1 => '邮件',
            2 => 'ServerChan',
            3 => 'Bark',
            4 => 'Telegram',
            5 => '微信企业',
            6 => 'TG酱',
            7 => 'PushPlus',
            8 => '爱语飞飞',
            9 => 'PushDear',
            10 => '钉钉',
        ],
    ],

    'language' => [
        'de' => ['Deutsch', 'de', 'de-DE', 'd.m.Y'],
        'en' => ['English', 'us', 'en-US', 'F d, Y'],
        'fa' => ['فارسی', 'ir', 'fa-IR', 'Y/m/d'],
        'ja' => ['日本語', 'jp', 'ja-JP', 'Y年m月d日'],
        'ko' => ['한국어', 'kr', 'ko-KR', 'Y년 m월 d일'],
        'vi' => ['Tiếng Việt', 'vn', 'vi-VN', 'd/m/Y'],
        'zh_CN' => ['简体中文', 'cn', 'zh-CN', 'Y年m月d日'],
        'ru' => ['Русский', 'ru', 'ru', 'd.m.Y'],
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
];
