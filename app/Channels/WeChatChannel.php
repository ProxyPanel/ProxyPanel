<?php

namespace App\Channels;

use App\Channels\Library\WeChat;
use Cache;
use Helpers;
use Http;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Log;
use Str;

class WeChatChannel
{
    public function send($notifiable, Notification $notification): false|array
    { // route('message.show', ['type' => 'markdownMsg', 'msgId' => ''])
        $message = $notification->toCustom($notifiable);

        $url = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token='.$this->getAccessToken();

        if (isset($message['button'])) { // 按钮交互型
            // https://work.weixin.qq.com/api/doc/90000/90135/90236#%E6%8C%89%E9%92%AE%E4%BA%A4%E4%BA%92%E5%9E%8B
            $body = [
                'touser' => '@all',
                'msgtype' => 'template_card',
                'agentid' => sysConfig('wechat_aid'),
                'template_card' => [
                    'card_type' => 'button_interaction',
                    'main_title' => ['title' => $message['title']],
                    'horizontal_content_list' => $message['body'],
                    'task_id' => time().Str::random(),
                    'button_list' => [
                        [
                            'type' => 1,
                            'text' => trans('common.status.reject'),
                            'style' => 3,
                            'url' => $message['button'][0],
                        ],
                        [
                            'type' => 1,
                            'text' => trans('common.confirm'),
                            'style' => 1,
                            'url' => $message['button'][1],
                        ],
                    ],
                ],
            ];
        } elseif (isset($message['url_type'])) { // 文本卡片
            $msgId = Str::uuid(); // 生成对公消息查询URL
            $body = [
                'touser' => '@all',
                'agentid' => sysConfig('wechat_aid'),
                'msgtype' => 'textcard',
                'textcard' => [
                    'title' => $message['title'],
                    'description' => '请点击下方按钮【查看详情】',
                    'url' => route('message.show', ['type' => $message['url_type'], $msgId]),
                    'btntxt' => '查看详情',
                ],
            ];
        } else { // 文本消息
            $body = [
                'touser' => '@all',
                'agentid' => sysConfig('wechat_aid'),
                'msgtype' => 'text',
                'text' => [
                    'content' => $message['content'],
                ],
                'duplicate_check_interval' => 600,
            ];
        }

        $response = Http::timeout(15)->withBody(json_encode($body, JSON_UNESCAPED_UNICODE), 'application/json;charset=utf-8')->post($url);

        // 发送成功
        if ($response->ok()) {
            $ret = $response->json();
            if (! $ret['errcode'] && $ret['errmsg'] === 'ok') {
                Helpers::addNotificationLog($message['title'], $message['content'] ?? var_export($message['body'], true), 5, 1, null, $msgId ?? null);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'] ?? var_export($message['body'], true), 5, -1, $ret ? $ret['errmsg'] : '未知');
        } else {
            Log::critical('[企业微信] 消息推送异常：'.var_export($response, true)); // 发送错误
        }

        return false;
    }

    private function getAccessToken(): ?string
    {
        if (Cache::has('wechat_access_token')) {
            $access_token = Cache::get('wechat_access_token');
        } else {
            // https://work.weixin.qq.com/api/doc/90000/90135/91039
            $response = Http::get('https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid='.sysConfig('wechat_cid').'&corpsecret='.sysConfig('wechat_secret'));
            if ($response->ok() && isset($response->json()['access_token'])) {
                $access_token = $response->json()['access_token'];
                Cache::put('wechat_access_token', $access_token, 7189); // 2小时
            } else {
                Log::critical('[企业微信] 消息推送异常：获取access_token失败！'.PHP_EOL.'携带访问参数：'.$response->body());
                abort(400);
            }
        }

        return $access_token ?? null;
    }

    public function verify(Request $request): void
    {
        $errCode = (new WeChat())->verifySignature($request->input('msg_signature'), $request->input('timestamp'), $request->input('nonce'), $request->input('echostr'), $sEchoStr);
        if ($errCode === 0) {
            exit($sEchoStr);
        }

        Log::critical('[企业微信] 互动消息推送异常：'.var_export($errCode, true));
    }
}
