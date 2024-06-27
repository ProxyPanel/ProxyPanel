<?php

if (config('app.key') && config('settings')) {
    Route::domain(sysConfig('subscribe_domain') ?: sysConfig('website_url'))
        ->get('s/{code}', 'User\SubscribeController@getSubscribeByCode')->name('sub'); // 节点订阅地址

    Route::domain(sysConfig('website_callback_url') ?: sysConfig('website_url'))
        ->match(['get', 'post'], 'callback/notify', 'PaymentController@notify')->name('payment.notify'); //支付回调
}

Route::get('callback/checkout', '\App\Payments\PayPal@getCheckout')->name('paypal.checkout'); // 支付回调相关
Route::post('api/telegram/webhook', 'TelegramController@webhook')->middleware('telegram'); // Telegram fallback
Route::get('api/wechat/verify', '\App\Channels\WeChatChannel@verify')->name('wechat.verify'); // 微信回调验证
Route::get('/message/{type}/{msg_id}/show', 'MessageController@index')->name('message.show'); // 微信回调验证

// 登录相关
Route::middleware(['isForbidden', 'affiliate', 'isMaintenance'])->group(function () {
    Route::get('lang/{locale}', 'AuthController@switchLang')->name('lang'); // 语言切换
    Route::get('login', 'AuthController@showLoginForm')->middleware('isSecurity')->name('login'); // 登录页面

    Route::prefix('oauth')->name('oauth.')->group(function () { // 用户第三方登录默认登录/转跳方式
        Route::get('{type}/login', 'OAuthController@logining')->name('login');
        Route::get('{type}/bind', 'OAuthController@binding')->name('bind');
        Route::get('{type}/register', 'OAuthController@register')->name('register');
        Route::get('{type}/redirect', 'OAuthController@simple')->name('simple');
        Route::get('{type}/unsubscribe', 'OAuthController@unsubscribe')->name('unsubscribe');
        Route::get('{type}/route', 'OAuthController@route')->name('route');
    });

    Route::post('login', 'AuthController@login')->middleware('isSecurity'); // 登录
    Route::get('logout', 'AuthController@logout')->name('logout'); // 退出
    Route::get('register', 'AuthController@showRegistrationForm')->name('register'); // 注册
    Route::post('register', 'AuthController@register'); // 注册
    Route::match(['get', 'post'], 'reset', 'AuthController@resetPassword')->name('resetPasswd'); // 重设密码
    Route::match(['get', 'post'], 'reset/{token}', 'AuthController@reset')->name('resettingPasswd'); // 重设密码
    Route::match(['get', 'post'], 'activeUser', 'AuthController@activeUser')->name('active'); // 激活账号
    Route::get('active/{token}', 'AuthController@active')->name('activeAccount'); // 激活账号
    Route::post('send', 'AuthController@sendCode')->name('sendVerificationCode'); // 发送注册验证码
    Route::get('free', 'AuthController@free')->name('freeInvitationCode'); // 免费邀请码
    Route::get('create/string', '\Illuminate\Support\Str@random')->name('createStr'); // 生成随机密码
    Route::get('create/uuid', '\Illuminate\Support\Str@uuid')->name('createUUID'); // 生成UUID
    Route::get('getPort', '\App\Components\Helpers@getPort')->name('getPort'); // 获取端口
});
Route::get('admin/login', 'AuthController@showLoginForm')->name('admin.login')->middleware('isForbidden', 'isSecurity'); // 管理登录页面
Route::post('admin/login', 'AuthController@login')->middleware('isSecurity')->name('admin.login.post'); // 管理登录
