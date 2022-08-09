<?php

namespace App\Channels;

use Cache;
use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;

class ServerChanChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toCustom($notifiable);

        $cacheDayKey = 'serverChanCountDays';
        $cacheMinuteKey = 'serverChanCountMinutes';
        if (Cache::has($cacheDayKey)) {
            Cache::increment($cacheDayKey);
        } else {
            Cache::put($cacheDayKey, 1, Day); // 天限制
        }

        if (Cache::has($cacheMinuteKey)) {
            Cache::increment($cacheMinuteKey);
        } else {
            Cache::put($cacheMinuteKey, 1, Minute); // 分钟限制
        }

        if (Cache::get($cacheDayKey) < 1000) { // 订阅会员 一天仅可发送不超过1000条
            if (Cache::get($cacheMinuteKey) < 5) {
                $response = Http::timeout(15)
                    ->post('https://sctapi.ftqq.com/'.sysConfig('server_chan_key').'.send?title='.urlencode($message['title']).'&desp='.urlencode($message['content']));
            } else {
                Log::critical('[ServerChan] 消息推送异常：分钟频率过高，请优化通知场景！');

                return false;
            }
        } else {
            Log::critical('[ServerChan] 消息推送异常：今日限额已耗尽！');

            return false;
        }

        // 发送成功
        if ($response->ok()) {
            $ret = $response->json();
            if (! $ret['errno']) {
                Helpers::addNotificationLog($message['title'], $message['content'], 2);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'], 2, -1, $ret ? $ret['errmsg'] : '未知');

            return false;
        }
        // 发送错误
        Log::critical('[ServerChan] 消息推送异常：'.var_export($response, true));

        return false;
    }
}
