<?php

return [
    'payment' => [
        'labels' => [
            'bitpayx' => '麻瓜宝',
            'codepay' => '码支付',
            'credit' => '余额',
            'epay' => '易支付',
            'f2fpay' => '支付宝当面付',
            'manual' => '人工支付',
            'paybeaver' => '海狸支付',
            'payjs' => 'PayJs',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'theadpay' => '平头哥支付',
            'youzan' => '有赞云',
        ],
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
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'google' => 'Google',
            'github' => 'GitHub',
            'gitlab' => 'GitLab',
            'bitbucket' => 'Bitbucket',
            'telegram' => 'Telegram',
        ],
        'icon' => [
            'facebook' => 'fa-facebook',
            'twitter' => 'fa-twitter',
            'linkedin' => 'fa-linkedin',
            'google' => 'fa-google',
            'github' => 'fa-github',
            'gitlab' => 'fa-gitlab',
            'bitbucket' => 'fa-bitbucket',
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
        'zh_CN' => ['简体中文', 'cn'],
        'en' => ['English', 'us'],
        'ko' => ['한국어', 'kr'],
        'vi' => ['Tiếng Việt', 'vn'],
    ],

    'currency' => [
        'cn' => ['name' => '人民币', 'code' => 'CNY', 'symbol' => '¥'],
        'us' => ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$'],
        'gb' => ['name' => 'Pound sterling', 'code' => 'GBP', 'symbol' => '£'],
        'hk' => ['name' => '港元', 'code' => 'HKD', 'symbol' => 'HK$'],
        'sg' => ['name' => 'Singapore dollar', 'code' => 'SGD', 'symbol' => 'S$'],
        'eu' => ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€'],
        'jp' => ['name' => '日本円', 'code' => 'JPY', 'symbol' => '¥'],
        'kr' => ['name' => '대한민국 원', 'code' => 'KRW', 'symbol' => '₩'],
        'ca' => ['name' => 'Canadian dollar', 'code' => 'CAD', 'symbol' => 'C$'],
        'tw' => ['name' => '新臺幣', 'code' => 'TWD', 'symbol' => 'NT$'],
    ],

    'contact' => [
        'telegram' => env('CONTACT_TELEGRAM', 'https://t.me/+nW8AwsPPUsliYzg1'),
        'qq' => env('CONTACT_QQ', null),
    ],
];
