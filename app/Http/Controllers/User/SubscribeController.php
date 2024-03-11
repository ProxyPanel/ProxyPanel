<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserSubscribe;
use App\Models\UserSubscribeLog;
use App\Services\ProxyService;
use App\Utils\IP;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Redirect;
use Response;

class SubscribeController extends Controller
{
    private static ?int $subType;

    private ProxyService $proxyServer;

    // 通过订阅码获取订阅信息
    public function getSubscribeByCode(Request $request, string $code): RedirectResponse|Response|string
    {
        preg_match('/[0-9A-Za-z]+/', $code, $matches, PREG_UNMATCHED_AS_NULL);

        if (empty($matches) || empty($code)) {
            return Redirect::route('login');
        }
        $code = $matches[0];
        self::$subType = is_numeric($request->input('type')) ? $request->input('type') : null;

        // 检查订阅码是否有效
        $subscribe = UserSubscribe::whereCode($code)->first();
        $this->proxyServer = new ProxyService();
        if (! $subscribe) {
            return $this->failed(trans('errors.subscribe.unknown'));
        }

        if ($subscribe->status !== 1) {
            return $this->failed(trans('errors.subscribe.sub_baned'));
        }

        // 检查用户是否有效
        $user = $subscribe->user;
        $this->proxyServer->setUser($user);
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

            if ($user->expiration_date < date('Y-m-d')) {
                return $this->failed(trans('errors.subscribe.expired'));
            }

            return $this->failed(trans('errors.subscribe.question'));
        }

        $subscribe->increment('times'); // 更新访问次数
        $this->subscribeLog($subscribe->id, IP::getClientIp(), json_encode(['Host' => $request->getHost(), 'User-Agent' => $request->userAgent()])); // 记录每次请求

        return $this->proxyServer->getProxyText(strtolower($request->input('target') ?? ($request->userAgent() ?? '')), self::$subType);
    }

    private function failed(string $text): \Illuminate\Http\Response
    { // 抛出错误的节点信息，用于兼容防止客户端订阅失败
        return Response::make(base64url_encode($this->proxyServer->failedProxyReturn($text, self::$subType ?? 1)));
    }

    private function subscribeLog(int $subscribeId, ?string $ip, string $headers): void
    { // 写入订阅访问日志
        $log = new UserSubscribeLog();
        $log->user_subscribe_id = $subscribeId;
        $log->request_ip = $ip;
        $log->request_time = now();
        $log->request_header = $headers;
        $log->save();
    }
}
