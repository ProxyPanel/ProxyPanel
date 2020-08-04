<?php

namespace App\Components;

use Overtrue\EasySms\EasySms;

/**
 * 发送短信
 *
 * 参考文档：\vendor\overtrue\easy-sms\README.md
 *
 * Class Sms
 *
 * @package App\Components
 */
class Sms
{
    public static function send()
    {

        $config = [
            // HTTP 请求的超时时间（秒）
            'timeout'  => 5.0,

            // 默认发送配置
            'default'  => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

                // 默认可用的发送网关
                'gateways' => [
                    'yunpian',
                ],
            ],

            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => '/tmp/easy-sms.log',
                ],
                'yunpian'  => [
                    'api_key' => '0c9c87c41aac355520d47d3c84e5a532',
                ],
            ],
        ];

        $easySms = new EasySms($config);

        $result = $easySms->send(15960271718, [
            'content'  => '您的验证码为: 6379',
            'template' => '2189086',
            'data'     => [
                'code' => 6379
            ],
        ]);

        return $result;
    }
}
