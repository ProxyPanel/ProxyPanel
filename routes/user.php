<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\User\AffiliateController;
use App\Http\Controllers\User\ArticleController;
use App\Http\Controllers\User\InviteController;
use App\Http\Controllers\User\InvoiceController;
use App\Http\Controllers\User\NodeController;
use App\Http\Controllers\User\ShopController;
use App\Http\Controllers\User\TicketController;
use App\Http\Controllers\UserController;
use App\Utils\Avatar;
use App\Utils\Helpers;
use App\Utils\Payments\Manual;
use App\Utils\Payments\Stripe;
use Illuminate\Support\Str;

Route::controller(UserController::class)->group(function () {
    Route::get('/', 'index')->name('home'); // 用户首页
    Route::post('exchange/subscribe', 'exchangeSubscribe')->name('changeSub'); // 更换节点订阅地址
    Route::post('checkIn', 'checkIn')->name('checkIn'); // 签到
    Route::get('profile', 'profile')->name('profile.show'); // 查看个人信息
    Route::post('profile', 'updateProfile')->name('profile.update'); // 修改个人信息
    Route::post('switch/admin', 'switchToAdmin')->name('switch'); // 转换成管理员的身份
    Route::get('currency/{code}', 'switchCurrency')->name('currency'); // 货币切换
});

Route::prefix('shop')->name('shop.')->controller(ShopController::class)->group(function () {
    Route::get('/', 'index')->name('index'); // 商品页面
    Route::get('/{good}', 'show')->name('show'); // 商品详细
    Route::post('/{good}/coupon/redeem', 'checkBonus')->name('coupon.check'); // 兑换优惠券码
    Route::post('/coupon/redeem', 'redeemCoupon')->name('coupon.redeem'); // 卡券余额充值
    Route::post('reset-traffic', 'resetTraffic')->name('resetTraffic'); // 重置用户流量
});

Route::prefix('invite')->name('invite.')->controller(InviteController::class)->group(function () {
    Route::get('/', 'index')->name('index'); // 邀请码
    Route::post('/', 'store')->name('store'); // 生成邀请码
});

Route::prefix('invoice')->name('invoice.')->controller(InvoiceController::class)->group(function () {
    Route::get('/', 'index')->name('index'); // 订单列表
    Route::get('/{sn}', 'show')->name('show'); // 订单详情
    Route::post('activate', 'activate')->name('activate'); // 激活预支付套餐
});

Route::prefix('node')->name('node.')->controller(NodeController::class)->group(function () {
    Route::get('/', 'index')->name('index'); // 节点列表
    Route::post('/{node}', 'show')->name('show'); // 节点详情
});

Route::prefix('knowledge')->name('knowledge.')->controller(ArticleController::class)->group(function () {
    Route::get('/', 'index')->name('index'); // 文章帮助中心
    Route::get('/{article}', 'show')->name('show'); // 文章详情
});

Route::prefix('ticket')->name('ticket.')->controller(TicketController::class)->group(function () {
    Route::get('/', 'index')->name('index'); // 工单列表
    Route::post('/', 'store')->name('store'); // 创建工单
    Route::get('{ticket}', 'edit')->name('edit'); // 查阅工单
    Route::put('{ticket}', 'reply')->name('reply'); // 回复工单
    Route::patch('{ticket}', 'close')->name('close'); // 关闭工单
});

Route::prefix('referral')->name('referral.')->controller(AffiliateController::class)->group(function () {
    Route::get('/', 'index')->name('index'); // 推广返利
    Route::post('/withdraw', 'withdraw')->name('withdraw'); // 申请提现
});

Route::prefix('payment')->controller(PaymentController::class)->group(function () {
    Route::post('purchase', 'purchase')->name('purchase'); // 创建支付
    Route::get('getStatus', 'getStatus')->name('orderStatus'); // 获取支付单状态
    Route::put('{order}/close', 'close')->name('closeOrder'); // 关闭支付单
    Route::get('{trade_no}', 'detail')->name('orderDetail'); // 支付单详情
});

Route::prefix('pay')->group(function () {
    Route::get('/manual/{payment}', [Manual::class, 'redirectPage'])->name('manual.checkout'); // 人工支付详细
    Route::post('/manual/{payment}/inform', [Manual::class, 'inform'])->name('manual.inform'); // 人工支付通知
    Route::get('/stripe/{session_id}', [Stripe::class, 'redirectPage'])->name('stripe.checkout'); // Stripe Checkout page
});

// 工具类路由
Route::prefix('tools')->group(function () {
    Route::get('create/string', [Str::class, 'random'])->name('createStr'); // 生成随机密码
    Route::get('create/uuid', [Str::class, 'uuid'])->name('createUUID'); // 生成UUID
    Route::get('get/avatar', [Avatar::class, 'get'])->name('getAvatar'); // 获取随机头像
    Route::get('get/port', [Helpers::class, 'getPort'])->name('getPort'); // 获取端口
});
