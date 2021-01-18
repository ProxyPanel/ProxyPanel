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

        $cacheKey = 'serverChanCount'.date('d');
        if (Cache::has($cacheKey)) {
            Cache::increment($cacheKey);
        } else {
            Cache::put($cacheKey, 1, Day); // 24小时
        }

        // 一天仅可发送不超过500条
        if (Cache::get($cacheKey) < 500) {
            $response = Http::timeout(15)
                ->get('https://sc.ftqq.com/'.sysConfig('server_chan_key').'.send?text='.$message['title'].'&desp='.urlencode($message['content']));
        } else {
            Log::error('ServerChan消息推送异常：今日500条限额已耗尽！');

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
            Helpers::addNotificationLog($message['title'], $message['content'], 2, 'admin', -1, $ret ? $ret['errmsg'] : '未知');

            return false;
        }
        // 发送错误
        Log::error('ServerChan消息推送异常：'.var_export($response, true));

        return false;
    }
}
