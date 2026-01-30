<?php

use App\Channels\WeChatChannel;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\User\SubscribeController;

// 订阅和支付回调路由（仅在配置完整时启用）
if (config('app.key') && config('settings')) {
    // 节点订阅路由
    Route::domain(sysConfig('subscribe_domain') ?: sysConfig('website_url'))->group(function () {
        Route::get('s/{code}', [SubscribeController::class, 'getSubscribeByCode'])->name('sub'); // 节点订阅
        Route::get('subscribe/{code}', [SubscribeController::class, 'index'])->name('subscribe.index'); // 节点订阅页面
    });

    // 支付回调路由
    Route::domain(sysConfig('payment_callback_url') ?: sysConfig('website_url'))->match(['get', 'post'], 'callback/notify', [PaymentController::class, 'notify'])->name('payment.notify'); // 支付回调
}

// API Webhook 路由
Route::post('api/telegram/webhook', [TelegramController::class, 'webhook'])->middleware('telegram'); // Telegram webhook
Route::get('api/wechat/verify', [WeChatChannel::class, 'verify'])->name('wechat.verify'); // 微信回调验证
Route::get('/message/{type}/{msg_id}/show', [MessageController::class, 'index'])->name('message.show'); // 消息展示

// 认证相关路由
Route::middleware(['isForbidden', 'affiliate', 'isMaintenance'])->group(function () {
    // OAuth 第三方登录
    Route::prefix('oauth')->name('oauth.')->controller(OAuthController::class)->group(function () {
        Route::get('{provider}/redirect/{operation}', 'redirect')->whereIn('operation', ['bind', 'register', 'login'])->name('route'); // 转跳
        Route::get('{provider}/unbind', 'unbind')->name('unbind'); // 解绑
        Route::get('{provider}/login', 'login')->name('login'); // 登录 callback
        Route::get('{provider}/register', 'register')->name('register'); // 注册 callback
        Route::get('{provider}/bind', 'bind')->name('bind'); // 绑定 callback
    });

    // 认证相关路由
    Route::controller(AuthController::class)->group(function () {
        Route::get('lang/{locale}', 'switchLang')->name('lang')->withoutMiddleware('isMaintenance'); // 语言切换
        Route::get('login', 'showLoginForm')->middleware('isSecurity')->name('login'); // 登录页面
        Route::post('login', 'login')->middleware('isSecurity'); // 登录
        Route::get('logout', 'logout')->name('logout'); // 退出
        Route::get('register', 'showRegistrationForm')->name('register'); // 注册
        Route::post('register', 'register'); // 注册
        Route::match(['get', 'post'], 'reset', 'resetPassword')->name('resetPasswd'); // 重设密码
        Route::match(['get', 'post'], 'reset/{token}', 'reset')->name('resettingPasswd'); // 重设密码
        Route::match(['get', 'post'], 'activeUser', 'activeUser')->name('active'); // 激活账号
        Route::get('active/{token}', 'active')->name('activeAccount'); // 激活账号
        Route::post('send', 'sendCode')->name('sendVerificationCode'); // 发送注册验证码
        Route::get('free', 'free')->name('freeInvitationCode'); // 免费邀请码
    });
});

// 管理员登录路由
Route::get('admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login')->middleware('isForbidden', 'isSecurity'); // 管理员登录页面
Route::post('admin/login', [AuthController::class, 'login'])->middleware('isSecurity')->name('admin.login.post'); // 管理员登录
