<?php


namespace App\Components;

use Cache;
use Http;
use Log;

class PushNotification
{
    public static function send($title, $content)
    {
        switch (sysConfig('is_notification')) {
            case 'serverChan':
                return self::ServerChan($title, $content);
            case 'bark':
                return self::Bark($title, $content);
            default:
                return false;
        }
    }

    /**
     * ServerChan推送消息
     *
     * @param  string  $title  消息标题
     * @param  string  $content  消息内容
     *
     * @return mixed
     */
    private static function ServerChan(string $title, string $content)
    {
        $cacheKey = 'serverChanCount'.date('d');
        if (Cache::has($cacheKey)) {
            Cache::increment($cacheKey);
        } else {
            Cache::put($cacheKey, 1, Day); // 24小时
        }

        // 一天仅可发送不超过500条
        if (Cache::get($cacheKey) < 500) {
            $response = Http::timeout(15)->get('https://sc.ftqq.com/'.sysConfig('server_chan_key').'.send?text='.$title.'&desp='.urlencode($content));
        } else {
            Log::error('ServerChan消息推送异常：今日500条限额已耗尽！');

            return false;
        }

        // 发送成功
        if ($response->ok()) {
            $message = $response->json();
            if (!$message['errno']) {
                Helpers::addNotificationLog($title, $content, 2);

                return $message;
            }
            // 发送失败
            Helpers::addNotificationLog($title, $content, 2, 'admin', $message ? $message['errmsg'] : '未知');

            return false;
        }
        // 发送错误
        Log::error('ServerChan消息推送异常：'.var_export($response, true));

        return false;
    }

    /**
     * Bark推送消息
     *
     * @param  string  $title  消息标题
     * @param  string  $content  消息内容
     *
     * @return mixed
     */
    private static function Bark(string $title, string $content)
    {
        $response = Http::timeout(15)->get('https://api.day.app/'.sysConfig('bark_key').'/'.$title.'/'.$content);

        if ($response->ok()) {
            $message = $response->json();
            // 发送成功
            if ($message['code'] === 200) {
                Helpers::addNotificationLog($title, $content, 3);

                return $message;
            }
            // 发送失败
            Helpers::addNotificationLog($title, $content, 3, 'admin', $message);

            return false;
        }
        // 发送错误
        Log::error('Bark消息推送异常：'.var_export($response, true));

        return false;
    }
}
