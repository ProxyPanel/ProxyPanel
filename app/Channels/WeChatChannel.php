<?php

namespace App\Channels;

use App\Channels\Components\WeChat;
use Cache;
use Helpers;
use Http;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Notification;
use Log;
use Str;

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
                $this->access_token = $response->json()['access_token'];
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

        if (isset($message['button'])) {
            // https://work.weixin.qq.com/api/doc/90000/90135/90236#%E6%8C%89%E9%92%AE%E4%BA%A4%E4%BA%92%E5%9E%8B
            $body = [
                'touser'        => '@all',
                'msgtype'       => 'template_card',
                'agentid'       => sysConfig('wechat_aid'),
                'template_card' => [
                    'card_type'               => 'button_interaction',
                    'main_title'              => ['title' => $message['title']],
                    'horizontal_content_list' => $message['body'],
                    'task_id'                 => time().Str::random(),
                    'button_list'             => [
                        [
                            'type'  => 1,
                            'text'  => '否 決',
                            'style' => 3,
                            'url'   => $message['button'][0],
                        ],
                        [
                            'type'  => 1,
                            'text'  => '确 认',
                            'style' => 1,
                            'url'   => $message['button'][1],
                        ],
                    ],
                ],
            ];
        } else {
            $body = [
                'touser'  => '@all',
                'agentid' => sysConfig('wechat_aid'),
                'msgtype' => 'text',
                'text'    => ['content' => Markdown::parse($message['content'])->toHtml()],
            ];
        }

        $response = Http::timeout(15)->post($url, $body);

        // 发送成功
        if ($response->ok()) {
            $ret = $response->json();
            if (! $ret['errcode'] && $ret['errmsg'] === 'ok') {
                Helpers::addNotificationLog($message['title'], $message['content'] ?? var_export($message['body'], true), 5);

                return $ret;
            }
            // 发送失败
            Helpers::addNotificationLog($message['title'], $message['content'] ?? var_export($message['body'], true), 5, 'admin', -1, $ret ? $ret['errmsg'] : '未知');

            return false;
        }
        // 发送错误
        Log::critical('Wechat消息推送异常：'.var_export($response, true));

        return false;
    }

    public function verify(Request $request)
    {
        $errCode = (new WeChat())->VerifyURL($request->input('msg_signature'), $request->input('timestamp'), $request->input('nonce'), $request->input('echostr'), $sEchoStr);
        if ($errCode === 0) {
            exit($sEchoStr);
        }

        Log::critical('Wechat互动消息推送异常：'.var_export($errCode, true));
    }
}
