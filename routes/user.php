<?php

Route::get('/', 'UserController@index')->name('home'); // 用户首页
Route::get('article/{article}', 'UserController@article')->name('article'); // 文章详情
Route::post('exchangeSubscribe', 'UserController@exchangeSubscribe')->name('changeSub'); // 更换节点订阅地址
Route::match(['get', 'post'], 'nodeList', 'UserController@nodeList')->name('node'); // 节点列表
Route::post('checkIn', 'UserController@checkIn')->name('checkIn'); // 签到
Route::get('services', 'UserController@services')->name('shop'); // 商品列表
Route::get('tickets', 'UserController@ticketList')->name('ticket'); // 工单
Route::post('createTicket', 'UserController@createTicket')->name('openTicket'); // 快速添加工单
Route::match(['get', 'post'], 'replyTicket', 'UserController@replyTicket')->name('replyTicket'); // 回复工单
Route::post('closeTicket', 'UserController@closeTicket')->name('closeTicket'); // 关闭工单
Route::get('invoices', 'UserController@invoices')->name('invoice'); // 订单列表
Route::post('closePlan', 'UserController@closePlan')->name('cancelPlan'); // 激活预支付套餐
Route::get('invoice/{sn}', 'UserController@invoiceDetail')->name('invoiceInfo'); // 订单明细
Route::post('resetUserTraffic', 'UserController@resetUserTraffic')->name('resetTraffic'); // 重置用户流量
Route::post('buy/{good}/redeem', 'UserController@redeemCoupon')->name('redeemCoupon'); // 使用优惠券
Route::get('buy/{good}', 'UserController@buy')->name('buy'); // 购买商品
Route::get('invite', 'UserController@invite')->name('invite'); // 邀请码
Route::post('makeInvite', 'UserController@makeInvite')->name('createInvite'); // 生成邀请码
Route::match(['get', 'post'], 'profile', 'UserController@profile')->name('profile'); // 修改个人信息
Route::post('switchToAdmin', 'UserController@switchToAdmin')->name('switch'); // 转换成管理员的身份
Route::post('charge', 'UserController@charge')->name('recharge'); // 卡券余额充值
Route::get('help', 'UserController@help')->name('help'); // 帮助中心

Route::namespace('User')->group(function () {
    Route::get('referral', 'AffiliateController@referral')->name('commission'); // 推广返利
    Route::post('extractMoney', 'AffiliateController@extractMoney')->name('applyCommission'); // 申请提现
});

Route::prefix('payment')->group(function () {
    Route::post('purchase', 'PaymentController@purchase')->name('purchase'); // 创建支付
    Route::get('getStatus', 'PaymentController@getStatus')->name('orderStatus'); // 获取支付单状态
    Route::put('{order}/close', 'PaymentController@close')->name('closeOrder'); // 关闭支付单
    Route::get('{trade_no}', 'PaymentController@detail')->name('orderDetail'); // 支付单详情
});

Route::prefix('pay')->group(function () {
    Route::get('/manual/{payment}', 'Gateway\Manual@redirectPage')->name('manual.checkout'); // 人工支付详细
    Route::post('/manual/{payment}/inform', 'Gateway\Manual@inform')->name('manual.inform'); // 人工支付通知
    Route::get('/stripe/{session_id}', 'Gateway\Stripe@redirectPage')->name('stripe.checkout'); // Stripe Checkout page
});
