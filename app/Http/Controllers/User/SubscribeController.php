<?php

namespace App\Http\Controllers\User;

use App\Components\IP;
use App\Http\Controllers\Controller;
use App\Models\UserSubscribe;
use App\Models\UserSubscribeLog;
use Arr;
use Illuminate\Http\Request;
use Redirect;
use Response;

class SubscribeController extends Controller
{
    private $subType;

    // 通过订阅码获取订阅信息
    public function getSubscribeByCode(Request $request, $code)
    {
        if (empty($code)) {
            return Redirect::route('login');
        }
        $this->subType = $request->input('type');

        // 检查订阅码是否有效
        $subscribe = UserSubscribe::whereCode($code)->first();
        if (! $subscribe) {
            return $this->failed('使用链接错误！请重新获取！');
        }

        if ($subscribe->status !== 1) {
            return $this->failed('链接已被封禁，请前往官网查询原因！');
        }

        // 检查用户是否有效
        $user = $subscribe->user;
        if (! $user) {
            return $this->failed('错误链接，账号不存在！请重新获取链接');
        }

        if ($user->status === -1) {
            return $this->failed('账号被禁用!');
        }

        if ($user->enable !== 1) {
            if ($user->ban_time) {
                return $this->failed('账号封禁至'.date('m-d H:i', $user->ban_time).',请解封后再更新！');
            }

            $unusedTransfer = $user->transfer_enable - $user->u - $user->d;
            if ($unusedTransfer <= 0) {
                return $this->failed('流量耗尽！请重新购买或重置流量！');
            }

            if ($user->expired_at < date('Y-m-d')) {
                return $this->failed('账号过期！请续费！');
            }

            return $this->failed('账号存在问题，请前往官网查询！');
        }

        // 更新访问次数
        $subscribe->increment('times', 1);

        // 记录每次请求
        $this->subscribeLog($subscribe->id, IP::getClientIp(), $request->headers);

        // 获取这个账号可用节点
        $query = $user->whereIsSubscribe(1)->userAccessNodes();

        if ($this->subType === 1) {
            $query = $query->whereIn('type', [1, 4]);
        } elseif ($this->subType) {
            $query = $query->whereType($this->subType);
        }

        $nodeList = $query->orderByDesc('sort')->orderBy('id')->get()->toArray();
        if (empty($nodeList)) {
            return $this->failed('无可用节点');
        }

        // 打乱数组
        if (sysConfig('rand_subscribe')) {
            $nodeList = Arr::shuffle($nodeList);
        }

        $scheme = null;

        // 展示到期时间和剩余流量
        if (sysConfig('is_custom_subscribe')) {
            $scheme .= $this->infoGenerator('到期时间: '.$user->expired_at).$this->infoGenerator('剩余流量: '.flowAutoShow($user->transfer_enable - $user->u - $user->d));
        }

        // 控制客户端最多获取节点数
        foreach ($nodeList as $key => $node) {
            // 控制显示的节点数
            if (sysConfig('subscribe_max') && $key >= sysConfig('subscribe_max')) {
                break;
            }

            $scheme .= $this->getUserNodeInfo($user->id, $node['id'], 0).PHP_EOL;
        }

        $headers = [
            'Content-type'  => 'application/octet-stream; charset=utf-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            //'Content-Disposition' => 'attachment; filename='.$filename
        ];

        // 适配Quantumult的自定义订阅头
        if (sysConfig('is_custom_subscribe')) {
            $headers['Subscription-Userinfo'] = 'upload='.$user->u.'; download='.$user->d.'; total='.$user->transfer_enable.'; expire='.strtotime($user->expired_at);
        }

        return Response::make(base64url_encode($scheme), 200, $headers);
    }

    // 抛出错误的节点信息，用于兼容防止客户端订阅失败
    private function failed($text)
    {
        return Response::make(base64url_encode($this->infoGenerator($text)), 200);
    }

    private function infoGenerator($text): string
    {
        $result = null;
        switch ($this->subType) {
            case 2:
                $result = 'vmess://'.base64url_encode(json_encode([
                    'v'    => '2', 'ps' => $text, 'add' => '0.0.0.0', 'port' => 0, 'id' => 0, 'aid' => 0, 'net' => 'tcp',
                    'type' => 'none', 'host' => '', 'path' => '/', 'tls' => 'tls',
                ], JSON_PRETTY_PRINT));
                break;
            case 3:
                $result = 'trojan://0@0.0.0.0:0?peer=0.0.0.0#'.rawurlencode($text);
                break;
            case 1:
            case 4:
            default:
                $result = 'ssr://'.base64url_encode('0.0.0.0:0:origin:none:plain:'.base64url_encode('0000').'/?obfsparam=&protoparam=&remarks='.base64url_encode($text).'&group='.base64url_encode(sysConfig('website_name')).'&udpport=0&uot=0');
                break;
        }

        return $result.PHP_EOL;
    }

    // 写入订阅访问日志
    private function subscribeLog($subscribeId, $ip, $headers): void
    {
        $log = new UserSubscribeLog();
        $log->user_subscribe_id = $subscribeId;
        $log->request_ip = $ip;
        $log->request_time = date('Y-m-d H:i:s');
        $log->request_header = $headers;
        $log->save();
    }
}
