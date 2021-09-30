<?php

namespace App\Channels;

use Cache;
use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;

class WeChatChannel
{
    private $access_token;

    public function __construct()
    {
        if (Cache::has('wechat_access_token')) {
            $this->access_token = Cache::get('wechat_access_token');
        } else {
            // https://work.weixin.qq.com/api/doc/90000/90135/91039
            $response = Http::get('https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid='.sysConfig('wechat_cid').'&corpsecret='.sysConfig('wechat_secret'));
            if ($response->ok() && isset($response->json()['access_token'])) {
                Cache::put('wechat_access_token', $response->json()['access_token'], 7200); // 2小时
            } else {
                Log::critical('Wechat消息推送异常：获取access_token失败！'.PHP_EOL.'携带访问参数：'.$response->body());
                abort(400);
            }
        }
    }

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toCustom($notifiable);

        $url = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token='.$this->access_token;
        $response = Http::timeout(15)->post($url, [
            'touser'                   => '@all',
            'agentid'                  => sysConfig('wechat_aid'),
            'msgtype'                  => 'text',
            'text'                     => ['content' => $message['content']],
            'duplicate_check_interval' => 600,
        ]);

        // 发送成功
        if ($response->ok()) {
            $ret = $response->json();
            if (! $ret['errcode'] && $ret['errmsg'] === 'ok') {
                Helpers::addNotificationLog($message['title'], $message['content'], 5);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'], 5, 'admin', -1, $ret ? $ret['errmsg'] : '未知');

            return false;
        }
        // 发送错误
        Log::critical('Wechat消息推送异常：'.var_export($response, true));

        return false;
    }
}
