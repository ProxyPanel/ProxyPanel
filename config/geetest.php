<?php

return [
    'lang'              => 'zh-cn',
    'server-get-config' => true,
    'id'                => env('GEETEST_ID'),
    'key'               => env('GEETEST_KEY'),
    'url'               => '/geetest',
    'protocol'          => 'http',
    'product'           => 'float',
    'client_fail_alert' => '请正确完成验证码操作',
    'server_fail_alert' => '验证码校验失败',
];
