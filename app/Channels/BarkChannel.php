<?php

namespace App\Channels;

use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;

class BarkChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toCustom($notifiable);
        $response = Http::timeout(15)->get('https://api.day.app/'.sysConfig('bark_key').'/'.$message['title'].'/'.$message['content']);

        if ($response->ok()) {
            $ret = $response->json();
            // 发送成功
            if ($ret['code'] === 200) {
                Helpers::addNotificationLog($message['title'], $message['content'], 3);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'], 3, 'admin', -1, $message);

            return false;
        }
        // 发送错误
        Log::error('Bark消息推送异常：'.var_export($response, true));

        return false;
    }
}
