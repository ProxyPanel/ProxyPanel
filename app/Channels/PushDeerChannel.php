<?php

namespace App\Channels;

use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;

class PushDeerChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toCustom($notifiable);

        $response = Http::timeout(15)
            ->post('https://api2.pushdeer.com/message/push?pushkey='.sysConfig('pushDeer_key').'&text='.urlencode($message['title']).'&desp='
                .urlencode($message['content']).'&type=markdown');

        // 发送成功
        if ($response->ok()) {
            $ret = $response->json();
            if (! $ret['code']) {
                Helpers::addNotificationLog($message['title'], $message['content'], 9);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'], 9, -1, $ret ? $ret['error'] : trans('common.status.unknown'));

            return false;
        }
        // 发送错误
        Log::critical(trans('notification.error', ['channel' => trans('admin.system.notification.channel.pushdeer'), 'reason' => var_export($response, true)]));

        return false;
    }
}
