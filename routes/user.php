<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\User\AffiliateController;
use App\Http\Controllers\UserController;
use App\Utils\Payments\Manual;
use App\Utils\Payments\Stripe;

Route::controller(UserController::class)->group(function () {
    Route::get('/', 'index')->name('home'); // 用户首页
    Route::get('article/{article}', 'article')->name('article'); // 文章详情
    Route::post('exchangeSubscribe', 'exchangeSubscribe')->name('changeSub'); // 更换节点订阅地址
    Route::match(['get', 'post'], 'nodeList', 'nodeList')->name('node'); // 节点列表
    Route::post('checkIn', 'checkIn')->name('checkIn'); // 签到
    Route::get('services', 'services')->name('shop'); // 商品列表
    Route::get('tickets', 'ticketList')->name('ticket'); // 工单
    Route::post('createTicket', 'createTicket')->name('openTicket'); // 快速添加工单
    Route::match(['get', 'post'], 'replyTicket', 'replyTicket')->name('replyTicket'); // 回复工单
    Route::post('closeTicket', 'closeTicket')->name('closeTicket'); // 关闭工单
    Route::get('invoices', 'invoices')->name('invoice'); // 订单列表
    Route::post('closePlan', 'closePlan')->name('cancelPlan'); // 激活预支付套餐
    Route::get('invoice/{sn}', 'invoiceDetail')->name('invoiceInfo'); // 订单明细
    Route::post('resetUserTraffic', 'resetUserTraffic')->name('resetTraffic'); // 重置用户流量
    Route::post('buy/{good}/redeem', 'redeemCoupon')->name('redeemCoupon'); // 使用优惠券
    Route::get('buy/{good}', 'buy')->name('buy'); // 购买商品
    Route::get('invite', 'invite')->name('invite'); // 邀请码
    Route::post('makeInvite', 'makeInvite')->name('createInvite'); // 生成邀请码
    Route::match(['get', 'post'], 'profile', 'profile')->name('profile'); // 修改个人信息
    Route::post('switchToAdmin', 'switchToAdmin')->name('switch'); // 转换成管理员的身份
    Route::post('charge', 'charge')->name('recharge'); // 卡券余额充值
    Route::get('knowledge', 'knowledge')->name('knowledge'); // 帮助中心
    Route::get('currency/{code}', 'switchCurrency')->name('currency'); // 语言切换
});
Route::controller(AffiliateController::class)->group(function () {
    Route::get('referral', 'referral')->name('commission'); // 推广返利
    Route::post('extractMoney', 'extractMoney')->name('applyCommission'); // 申请提现
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
