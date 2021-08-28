<?php

namespace App\Channels;

use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;

class PushPlusChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toCustom($notifiable);

        $response = Http::timeout(15)->post('https://www.pushplus.plus/send', [
            'token'    => sysConfig('pushplus_token'),
            'title'    => $message['title'],
            'content'  => $message['content'],
            'template' => 'markdown',
        ]);

        // 发送成功
        if ($response->ok()) {
            $ret = $response->json();
            if ($ret['code'] === 200) {
                Helpers::addNotificationLog($message['title'], $message['content'], 7);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'], 7, 'admin', -1, $ret ? $ret['msg'] : '未知');

            return false;
        }
        // 发送错误
        Log::error('PushPlus消息推送异常：'.var_export($response, true));

        return false;
    }
}
