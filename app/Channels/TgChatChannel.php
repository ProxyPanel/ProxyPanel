<?php

namespace App\Channels;

use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;

class TgChatChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toCustom($notifiable);

        $response = Http::timeout(15)->get('https://tgbot-red.vercel.app/api?token='.sysConfig('tg_chat_token').'&message='.$message['title'].PHP_EOL.'=========='.PHP_EOL.$message['content']);

        // 发送成功
        if ($response->ok()) {
            $ret = $response->json();
            if ($ret['code'] === 200) {
                Helpers::addNotificationLog($message['title'], $message['content'], 6);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'], 6, -1, $ret ? $ret['message'] : trans('common.status.unknown'));

            return false;
        }
        // 发送错误
        Log::critical(trans('notification.error', ['channel' => trans('admin.system.notification.channel.tg_chat'), 'reason' => var_export($response, true)]));

        return false;
    }
}
