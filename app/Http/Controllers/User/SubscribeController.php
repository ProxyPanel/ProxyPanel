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
        self::$subType = is_numeric($request->input('type')) ? $request->input('type') : null;
        // 检查订阅码是否有效
        if (! preg_match('/^[0-9A-Za-z]+$/', $code)) {
            $this->failed(trans('errors.subscribe.unknown'));
        }

        $subscribe = UserSubscribe::whereCode($code)->first();
        if (! $subscribe) {
            $this->failed(trans('errors.subscribe.unknown'));
        }

        if ($subscribe->status !== 1) {
            $this->failed(trans('errors.subscribe.sub_banned'));
        }

        $user = $subscribe->user;
        if (! $user) { // 检查用户是否有效
            $this->failed(trans('errors.subscribe.user'));
        }

        if ($user->status === -1) {
            $this->failed(trans('errors.subscribe.user_disabled'));
        }

        if ($user->enable !== 1) {
            if ($user->ban_time) {
                $this->failed(trans('errors.subscribe.banned_until', ['time' => $user->ban_time]));
            }

            if ($user->unused_traffic <= 0) {
                $this->failed(trans('errors.subscribe.out'));
            }

            if ($user->expiration_date < now()->toDateString()) {
                $this->failed(trans('errors.subscribe.expired'));
            }

            $this->failed(trans('errors.subscribe.question'));
        }
        $this->proxyServer->setUser($user);

        $subscribe->increment('times'); // 更新访问次数
        $this->subscribeLog($subscribe->id, IP::getClientIp(), json_encode(['Host' => $request->getHost(), 'User-Agent' => $request->userAgent()])); // 记录每次请求

        return $this->proxyServer->getProxyText(strtolower($request->input('target') ?? ($request->userAgent() ?? '')), self::$subType);
    }

    private function failed(string $text): void
    { // 抛出错误的节点信息，用于兼容防止客户端订阅失败
        $this->proxyServer->failedProxyReturn($text, self::$subType ?? 1);
    }

    private function subscribeLog(int $subscribeId, ?string $ip, string $headers): void
    { // 写入订阅访问日志
        $log = new UserSubscribeLog;
        $log->user_subscribe_id = $subscribeId;
        $log->request_ip = $ip;
        $log->request_time = now();
        $log->request_header = $headers;
        $log->save();
    }
}
