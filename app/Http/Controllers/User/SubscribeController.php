<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserSubscribe;
use App\Models\UserSubscribeLog;
use App\Services\ProxyService;
use App\Services\UserService;
use App\Utils\IP;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscribeController extends Controller
{
    private static ?int $subType;

    private ProxyService $proxyServer;

    public function __construct(ProxyService $proxyServer)
    {
        $this->proxyServer = $proxyServer;
    }

    public function index(Request $request, string $code)
    {
        // 检查订阅码格式
        if (! preg_match('/^[0-9A-Za-z]+$/', $code)) {
            return redirect()->route('login');
        }

        // 检查订阅码是否有效
        $subscribe = UserSubscribe::whereCode($code)->firstOrFail();
        $user = $subscribe->user;
        $userService = new UserService($user);

        return view('user.subscribe', [
            'remainDays' => $userService->getRemainingDays(),
            'unusedPercent' => $userService->getUnusedTrafficPercent(),
            'user' => $user,
            'subscribe' => $subscribe,
        ]);
    }

    public function getSubscribeByCode(Request $request, string $code): RedirectResponse|string
    { // 通过订阅码获取订阅信息
        self::$subType = is_numeric($request->input('type')) ? (int) $request->input('type') : null;

        // 检查订阅码格式
        if (! preg_match('/^[0-9A-Za-z]+$/', $code)) {
            return $this->failed(trans('errors.subscribe.unknown'));
        }

        // 检查订阅是否存在
        $subscribe = UserSubscribe::whereCode($code)->first();
        if (! $subscribe) {
            return $this->failed(trans('errors.subscribe.unknown'));
        }

        // 检查订阅状态
        if ($subscribe->status !== 1) {
            return $this->failed(trans('errors.subscribe.sub_banned'));
        }

        // 检查用户是否有效
        $user = $subscribe->user;
        if (! $user) {
            return $this->failed(trans('errors.subscribe.user'));
        }

        // 检查用户状态
        if ($user->status === -1) {
            return $this->failed(trans('errors.subscribe.user_disabled'));
        }

        if ($user->enable !== 1) {
            if ($user->ban_time) {
                return $this->failed(trans('errors.subscribe.banned_until', ['time' => $user->ban_time]));
            }

            if ($user->unused_traffic <= 0) {
                return $this->failed(trans('errors.subscribe.out'));
            }

            if ($user->expiration_date < now()->toDateString()) {
                return $this->failed(trans('errors.subscribe.expired'));
            }

            return $this->failed(trans('errors.subscribe.question'));
        }

        // 设置用户并更新订阅信息
        $this->proxyServer->setUser($user);
        $subscribe->increment('times'); // 更新访问次数

        // 记录订阅日志
        $this->subscribeLog($subscribe->id, IP::getClientIp(), json_encode([
            'Host' => $request->getHost(),
            'User-Agent' => $request->userAgent(),
        ]));

        // 返回订阅内容
        return $this->proxyServer->getProxyText(
            strtolower($request->input('target') ?? ($request->userAgent() ?? '')),
            self::$subType
        );
    }

    private function failed(string $text): string
    { // 抛出错误的节点信息，用于兼容防止客户端订阅失败
        $this->proxyServer->failedProxyReturn($text, self::$subType ?? 1);

        return '';
    }

    private function subscribeLog(int $subscribeId, ?string $ip, string $headers): void
    { // 写入订阅访问日志
        UserSubscribeLog::create([
            'user_subscribe_id' => $subscribeId,
            'request_ip' => $ip,
            'request_time' => now(),
            'request_header' => $headers,
        ]);
    }
}
