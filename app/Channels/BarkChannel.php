<?php

namespace App\Channels;

use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Log;
use Str;

class BarkChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toBark')) {
            $message = $notification->toBark($notifiable);
        } else {
            $message = $notification->toCustom($notifiable);
        }

        if (isset($message['url_type'])) { // 生成对公消息查询URL
            $msgId = Str::uuid();
            $message['url'] = route('message.show', ['type' => $message['url_type'], $msgId]);
            unset($message['url_type']);
        }

        $response = Http::timeout(15)
            ->get('https://api.day.app/'.sysConfig('bark_key')."/{$message['title']}/{$message['content']}?".http_build_query(Arr::except($message, ['title', 'content'])));

        if ($response->ok()) {
            $ret = $response->json();
            // 发送成功
            if ($ret['code'] === 200) {
                Helpers::addNotificationLog($message['title'], $message['content'], 3, 1, null, $msgId ?? null);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'], 3, -1);

            return false;
        }
        // 发送错误
        Log::critical('[Bark] 消息推送异常：'.var_export($response, true));

        return false;
    }
}
