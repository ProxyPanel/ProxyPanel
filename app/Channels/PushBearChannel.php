<?php

namespace App\Channels;

use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;

class PushBearChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toCustom($notifiable);
        $response = Http::timeout(15)->get('https://pushbear.ftqq.com/sub', [
            'sendkey' => sysConfig('push_bear_send_key'),
            'text'    => $message['title'],
            'desp'    => $message['content'],
        ]);
        if ($response->ok()) {
            $ret = $response->json();
            // 发送成功
            if ($ret) {
                Helpers::addMarketing(2, $message['title'], $message['content']);

                return $ret;
            }
            // 发送失败
            Helpers::addMarketing(2, $message['title'], $message['content'], -1, $ret['message']);

            return false;
        }
        // 发送错误
        Log::error('Bark消息推送异常：'.var_export($response, true));

        return false;
    }
}
