<?php

namespace App\Http\Controllers\User;

use App\Components\IP;
use App\Http\Controllers\ClientController;
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
    public function getSubscribeByCode(Request $request, string $code)
    {
        if (empty($code)) {
            return Redirect::route('login');
        }
        $this->subType = $request->input('type');
        $target = strtolower($request->input('target') ?? ($request->userAgent() ?? ''));

        // 检查订阅码是否有效
        $subscribe = UserSubscribe::whereCode($code)->first();
        if (! $subscribe) {
            return $this->failed(trans('errors.subscribe.unknown'));
        }

        if ($subscribe->status !== 1) {
            return $this->failed(trans('errors.subscribe.sub_baned'));
        }

        // 检查用户是否有效
        $user = $subscribe->user;
        if (! $user) {
            return $this->failed(trans('errors.subscribe.user'));
        }

        if ($user->status === -1) {
            return $this->failed(trans('errors.subscribe.user_disable'));
        }

        if ($user->enable !== 1) {
            if ($user->ban_time) {
                return $this->failed(trans('errors.subscribe.baned_until', ['time' => $user->ban_time]));
            }

            $unusedTraffic = $user->transfer_enable - $user->used_traffic;
            if ($unusedTraffic <= 0) {
                return $this->failed(trans('errors.subscribe.out'));
            }

            if ($user->expired_at < date('Y-m-d')) {
                return $this->failed(trans('errors.subscribe.expired'));
            }

            return $this->failed(trans('errors.subscribe.question'));
        }

        // 更新访问次数
        $subscribe->increment('times', 1);

        // 记录每次请求
        $this->subscribeLog($subscribe->id, IP::getClientIp(), json_encode(['Host' => $request->getHost(), 'User-Agent' => $request->userAgent()]));

        // 获取这个账号可用节点
        $query = $user->nodes()->whereIsSubscribe(1);

        if ($this->subType === 1) {
            $query = $query->whereIn('type', [1, 4]);
        } elseif ($this->subType) {
            $query = $query->whereType($this->subType);
        }

        $nodeList = $query->orderByDesc('sort')->orderBy('id')->get();
        if (empty($nodeList)) {
            return $this->failed(trans('errors.subscribe.none'));
        }

        $servers = [];
        foreach ($nodeList as $node) {
            $servers[] = $node->getConfig($user);
        }

        // 打乱数组
        if (sysConfig('rand_subscribe')) {
            $servers = Arr::shuffle($servers);
        }

        if (sysConfig('subscribe_max')) {
            $servers = array_slice($servers, 0, (int) sysConfig('subscribe_max'));
        }

        return (new ClientController)->config($target, $user, $servers);
    }

    // 抛出错误的节点信息，用于兼容防止客户端订阅失败
    private function failed($text)
    {
        return Response::make(base64url_encode($this->infoGenerator($text)));
    }

    private function infoGenerator($text): string
    {
        switch ($this->subType) {
            case 2:
                $result = 'vmess://'.base64url_encode(json_encode([
                    'v' => '2', 'ps' => $text, 'add' => '0.0.0.0', 'port' => 0, 'id' => 0, 'aid' => 0, 'net' => 'tcp',
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
