<?php

namespace App\Channels;

use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;

class iYuuChannel
{
    private $token;

    public function __construct()
    {
        $this->token = sysConfig('iYuu_token');

        if ($this->token) {
            Log::critical('[爱语飞飞] TOKEN 为空');
        }
    }

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toCustom($notifiable);

        $response = Http::timeout(15)->post('https://iyuu.cn/'.$this->token.'.send?title='.urlencode($message['title']).'&desp='.urlencode($message['content']));

        // 发送成功
        if ($response->ok()) {
            $ret = $response->json();
            if (! $ret['errcode']) {
                Helpers::addNotificationLog($message['title'], $message['content'], 8);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'], 8, -1, $ret ? $ret['errmsg'] : '未知');

            return false;
        }
        // 发送错误
        Log::critical('[爱语飞飞] 消息推送异常：'.var_export($response, true));

        return false;
    }
}
