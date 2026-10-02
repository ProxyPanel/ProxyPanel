<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 登录：IP+账号那一档防撞库；IP 那一档只拦跨账号猜测，必须容得下共享出口 IP
        RateLimiter::for('login', function (Request $request) {
            return [
                // 账号列是大小写不敏感的比较，键也必须归一，否则换大小写就能绕开这一档
                Limit::perMinute(5)->by($request->ip().'|'.mb_strtolower(trim((string) $request->input('username')))),
                Limit::perMinute(60)->by($request->ip()),
            ];
        });

        // 注册：分钟档只拦脚本洪水；每 IP 的量由后台 register_ip_limit 决定，不要比它更紧
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // 会发邮件的三个入口合用一档，拦邮件轰炸而不是同网段的正常点击
        RateLimiter::for('mail-actions', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // 消费校验令牌：给穷举加成本，留出填错重试的余量
        RateLimiter::for('verify-token', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // 客户端 API 的登录与注册：这两个端点未鉴权可试账号，按 IP+账号 与按 IP 双档
        RateLimiter::for('client-auth', function (Request $request) {
            $account = mb_strtolower(trim((string) ($request->input('username') ?: $request->input('email'))));

            return [
                Limit::perMinute(5)->by($request->ip().'|'.$account),
                Limit::perMinute(30)->by($request->ip()),
            ];
        });

        // 支付回调：未鉴权且每条都写库，阈值要容得下聚合支付的批量回传
        RateLimiter::for('payment-callback', function (Request $request) {
            return Limit::perMinute(200)->by($request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware(['web', 'user'])
                ->group(base_path('routes/user.php'));

            Route::middleware(['web', 'admin'])
                ->group(base_path('routes/admin.php'));
        });
    }
}
