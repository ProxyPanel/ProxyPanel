<?php

namespace App\Channels;

use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;

class iYuuChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toCustom($notifiable);

        $response = Http::timeout(15)->post('https://iyuu.cn/'.sysConfig('iYuu_token').'.send?title='.urlencode($message['title']).'&desp='.urlencode($message['content']));

        // 发送成功
        if ($response->ok()) {
            $ret = $response->json();
            if (! $ret['errcode']) {
                Helpers::addNotificationLog($message['title'], $message['content'], 8);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'], 8, -1, $ret ? $ret['errmsg'] : trans('common.status.unknown'));

            return false;
        }
        // 发送错误
        Log::critical(trans('notification.error', ['channel' => trans('admin.system.notification.channel.iyuu'), 'reason' => var_export($response, true)]));

        return false;
    }
}
