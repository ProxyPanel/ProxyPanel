<?php

use App\Channels\WeChatChannel;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\User\SubscribeController;

if (config('app.key') && config('settings')) {
    Route::domain(sysConfig('subscribe_domain') ?: sysConfig('website_url'))->get('s/{code}', [SubscribeController::class, 'getSubscribeByCode'])->name('sub'); // 节点订阅地址

    Route::domain(sysConfig('website_callback_url') ?: sysConfig('website_url'))->match(['get', 'post'], 'callback/notify', [PaymentController::class, 'notify'])->name('payment.notify'); //支付回调
}

Route::post('api/telegram/webhook', [TelegramController::class, 'webhook'])->middleware('telegram'); // Telegram fallback
Route::get('api/wechat/verify', [WeChatChannel::class, 'verify'])->name('wechat.verify'); // 微信回调验证
Route::get('/message/{type}/{msg_id}/show', [MessageController::class, 'index'])->name('message.show'); // 微信回调验证

Route::middleware(['isForbidden', 'affiliate', 'isMaintenance'])->group(function () { // 登录相关
    Route::prefix('oauth')->name('oauth.')->controller(OAuthController::class)->group(function () { // 用户第三方登录默认登录/转跳方式
        Route::get('{provider}/redirect/{operation}', 'redirect')->whereIn('operation', ['bind', 'register', 'login'])->name('route'); // 转跳
        Route::get('{provider}/unbind', 'unbind')->name('unbind'); // 解绑
        Route::get('{provider}/login', 'login')->name('login'); // 登录 callback
        Route::get('{provider}/register', 'register')->name('register'); // 注册 callback
        Route::get('{provider}/bind', 'bind')->name('bind'); // 绑定 callback
    });

    Route::controller(AuthController::class)->group(function () {
        Route::get('lang/{locale}', 'switchLang')->name('lang'); // 语言切换
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
    Route::get('create/string', [Str::class, 'random'])->name('createStr'); // 生成随机密码
    Route::get('create/uuid', [Str::class, 'uuid'])->name('createUUID'); // 生成UUID
    Route::get('getPort', [Helpers::class, 'getPort'])->name('getPort'); // 获取端口
});
Route::get('admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login')->middleware('isForbidden', 'isSecurity'); // 管理登录页面
Route::post('admin/login', [AuthController::class, 'login'])->middleware('isSecurity')->name('admin.login.post'); // 管理登录
