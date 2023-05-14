<?php

namespace App\Channels;

use Cache;
use Helpers;
use Http;
use Illuminate\Notifications\Notification;
use Log;
use Str;

class DingTalkChannel
{
    // https://open.dingtalk.com/document/robots/custom-robot-access
    public function send($notifiable, Notification $notification)
    {
        $cacheKey = 'dingTalkCount'.date('d');
        if (Cache::has($cacheKey)) {
            Cache::increment($cacheKey);
        } else {
            Cache::put($cacheKey, 1, Minute); // 1分钟
        }

        if (Cache::get($cacheKey) > 20) { // 每个机器人每分钟最多发送20条消息到群里，如果超过20条，会限流10分钟。
            Log::critical('[钉钉] 消息推送异常：每个机器人每分钟最多发送20条消息到群里，如果超过20条，会限流10分钟。');

            return false;
        }

        $message = $notification->toCustom($notifiable);

        $url = 'https://oapi.dingtalk.com/robot/send?';

        $query['access_token'] = sysConfig('dingTalk_access_token');

        if (sysConfig('dingTalk_secret') !== null) {
            $timestamp = time() * 1000;
            $query['timestamp'] = $timestamp;
            $query['sign'] = $this->sign($timestamp);
        }
        $url .= http_build_query($query);

        if (isset($message['button'])) { // 独立跳转ActionCard类型
            $body = [
                'msgtype' => 'actionCard',
                'actionCard' => [
                    'title' => $message['title'],
                    'text' => $message['markdown'],
                    'btnOrientation' => 1,
                    'btns' => [
                        [
                            'title' => trans('common.status.reject'),
                            'actionURL' => $message['button'][0],
                        ],
                        [
                            'title' => trans('common.confirm'),
                            'actionURL' => $message['button'][1],
                        ],
                    ],
                ],
            ];
        } elseif (isset($message['url_type'])) { // 文本卡片
            $msgId = Str::uuid(); // 生成对公消息查询URL
            $body = [
                'msgtype' => 'link',
                'link' => [
                    'title' => $message['title'],
                    'text' => '请点击下方按钮【查看详情】',
                    'messageUrl' => route('message.show', ['type' => $message['url_type'], $msgId]),
                ],
            ];
        } else { // 文本消息
            $body = [
                'msgtype' => 'text',
                'text' => [
                    'content' => $message['content'],
                ],
            ];
        }

        $response = Http::timeout(15)->withBody(json_encode($body, JSON_UNESCAPED_UNICODE), 'application/json;charset=utf-8')->post($url);

        // 发送成功
        if ($response->ok()) {
            $ret = $response->json();
            if (! $ret['errcode'] && $ret['errmsg'] === 'ok') {
                Helpers::addNotificationLog($message['title'], $message['content'] ?? var_export($message['body'], true), 10, 1, null, $msgId ?? null);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'] ?? var_export($message['body'], true), 10, -1, $ret ? $ret['errmsg'] : '未知');

            return false;
        }
        // 发送错误
        Log::critical('[钉钉] 消息推送异常：'.var_export($response, true));

        return false;
    }

    private function sign(int $timestamp): string // 加签
    { // https://open.dingtalk.com/document/robots/customize-robot-security-settings
        return base64_encode(hash_hmac('sha256', $timestamp."\n".sysConfig('dingTalk_secret'), sysConfig('dingTalk_secret'), true));
    }
}
