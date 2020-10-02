<?php

Route::domain(sysConfig('subscribe_domain') ?: sysConfig('website_url'))->group(function () {
    Route::get('s/{code}', 'User\SubscribeController@getSubscribeByCode')->name('sub'); // 节点订阅地址
});

// 支付回调相关
Route::prefix('callback')->group(function () {
    Route::get('checkout', 'Gateway\PayPal@getCheckout')->name('paypal.checkout');
    Route::domain(sysConfig('website_callback_url') ?: sysConfig('website_url'))->group(function () {
        Route::get('notify', 'PaymentController@notify')->name('payment.notify'); //支付回调
    });
});

// 登录相关
Route::middleware(['isForbidden', 'affiliate', 'isMaintenance'])->group(function () {
    Route::get('lang/{locale}', 'AuthController@switchLang')->name('lang'); // 语言切换
    Route::any('login', 'AuthController@login')->middleware('isSecurity')->name('login'); // 登录
    Route::get('logout', 'AuthController@logout')->name('logout'); // 退出
    Route::any('register', 'AuthController@register')->name('register'); // 注册
    Route::any('reset', 'AuthController@resetPassword')->name('resetPasswd'); // 重设密码
    Route::any('reset/{token}', 'AuthController@reset')->name('resettingPasswd'); // 重设密码
    Route::any('activeUser', 'AuthController@activeUser')->name('active'); // 激活账号
    Route::get('active/{token}', 'AuthController@active')->name('activeAccount'); // 激活账号
    Route::post('send', 'AuthController@sendCode')->name('sendVerificationCode'); // 发送注册验证码
    Route::get('free', 'AuthController@free')->name('freeInvitationCode'); // 免费邀请码
    Route::get('create/string', '\Illuminate\Support\Str@random')->name('createStr'); // 生成随机密码
    Route::get('create/uuid', '\Illuminate\Support\Str@uuid')->name('createUUID'); // 生成UUID
});
Route::any('admin/login', 'AuthController@login')->name('admin.login')->middleware('isForbidden', 'isSecurity'); // 管理登录

// 用户相关
Route::middleware(['isForbidden', 'isMaintenance', 'isLogin'])->group(function () {
    Route::get('/', 'UserController@index')->name('home'); // 用户首页
    Route::get('article', 'UserController@article')->name('article'); // 文章详情
    Route::post('exchangeSubscribe', 'UserController@exchangeSubscribe')->name('changeSub'); // 更换节点订阅地址
    Route::any('nodeList', 'UserController@nodeList')->name('node'); // 节点列表
    Route::post('checkIn', 'UserController@checkIn')->name('checkIn'); // 签到
    Route::get('services', 'UserController@services')->name('shop'); // 商品列表
    Route::get('tickets', 'UserController@ticketList')->name('ticket'); // 工单
    Route::post('createTicket', 'UserController@createTicket')->name('openTicket'); // 快速添加工单
    Route::any('replyTicket', 'UserController@replyTicket')->name('replyTicket'); // 回复工单
    Route::post('closeTicket', 'UserController@closeTicket')->name('closeTicket'); // 关闭工单
    Route::get('invoices', 'UserController@invoices')->name('invoice'); // 订单列表
    Route::post('closePlan', 'UserController@closePlan')->name('cancelPlan'); // 激活预支付套餐
    Route::get('invoice/{sn}', 'UserController@invoiceDetail')->name('invoiceInfo'); // 订单明细
    Route::post('resetUserTraffic', 'UserController@resetUserTraffic')->name('resetTraffic'); // 重置用户流量
    Route::get('buy/{id}', 'UserController@buy')->name('buy'); // 购买商品
    Route::post('redeemCoupon', 'UserController@redeemCoupon')->name('redeemCoupon'); // 使用优惠券
    Route::get('invite', 'UserController@invite')->name('invite'); // 邀请码
    Route::post('makeInvite', 'UserController@makeInvite')->name('createInvite'); // 生成邀请码
    Route::any('profile', 'UserController@profile')->name('profile'); // 修改个人信息
    Route::post("switchToAdmin", "UserController@switchToAdmin")->name('switch'); // 转换成管理员的身份
    Route::post("charge", "UserController@charge")->name('recharge'); // 卡券余额充值
    Route::get("help", "UserController@help")->name('help'); // 帮助中心

    Route::namespace('User')->group(function () {
        Route::get('referral', 'AffiliateController@referral')->name('commission'); // 推广返利
        Route::post('extractMoney', 'AffiliateController@extractMoney')->name('applyCommission'); // 申请提现
    });

    Route::prefix('payment')->group(function () {
        Route::post('purchase', 'PaymentController@purchase')->name('purchase'); // 创建支付
        Route::post('close', 'PaymentController@close')->name('closeOrder'); // 关闭支付单
        Route::get('getStatus', 'PaymentController@getStatus')->name('orderStatus'); // 获取支付单状态
        Route::get('{trade_no}', 'PaymentController@detail')->name('orderDetail'); // 支付单详情
    });
});

// 管理相关
Route::middleware(['isForbidden', 'isAdminLogin', 'isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', 'AdminController@index')->name('index'); // 后台首页
    Route::any('profile', 'AdminController@profile')->name('profile'); // 修改个人信息
    Route::get('config', 'AdminController@config')->name('config'); // 系统设置
    Route::get('invite', 'AdminController@inviteList')->name('invite'); // 邀请码列表
    Route::post('invite', 'AdminController@makeInvite')->name('invite.create'); // 生成邀请码
    Route::get('Invite/export', 'AdminController@exportInvite')->name('invite.export'); // 导出邀请码
    Route::get('getPort', 'AdminController@getPort')->name('getPort'); // 生成端口

    Route::namespace('Admin')->group(function () {
        Route::resource('user', 'UserController')->except('show');
        Route::name('user.')->group(function () {
            Route::post('batchAdd', 'UserController@batchAddUsers')->name('batch'); // 批量生成账号
            Route::resource('group', 'UserGroupController')->except('show');// 用户分组管理
            Route::get('monitor/{id}', 'LogsController@userTrafficMonitor')->name('monitor'); // 用户流量监控
            Route::get("online/{id}", "LogsController@onlineIPMonitor")->name('online'); // 在线IP监控
            Route::post("switch", "UserController@switchToUser")->name('switch'); // 转换成某个用户的身份
            Route::post('updateCredit', 'UserController@handleUserCredit')->name('updateCredit'); // 用户余额充值
            Route::post('reset', 'UserController@resetTraffic')->name('reset'); // 重置用户流量
            Route::get('export/{id}', 'UserController@export')->name('export'); // 导出(查看)配置信息
            Route::post('export/{id}', 'UserController@exportProxyConfig')->name('exportProxy'); // 导出(查看)配置信息
        });

        Route::prefix('subscribe')->name('subscribe.')->group(function () {
            Route::get('/', 'SubscribeController@index')->name('index'); // 订阅码列表
            Route::get('log/{id}', 'SubscribeController@subscribeLog')->name('log'); // 订阅码记录
            Route::post('set/{id}', 'SubscribeController@setSubscribeStatus')->name('set'); // 启用禁用用户的订阅
        });

        Route::resource('ticket', 'TicketController')->except('create', 'show');
        Route::resource('article', 'ArticleController');
        Route::prefix('marketing')->name('marketing.')->group(function () {
            Route::get("email", "MarketingController@emailList")->name('email'); // 邮件消息列表
            Route::get("push", "MarketingController@pushList")->name('push'); // 推送消息列表
            Route::post("add", "MarketingController@addPushMarketing")->name('add'); // 推送消息
        });

        Route::resource('node', 'NodeController')->except('show');
        Route::prefix('node')->name('node.')->group(function () {
            Route::get('monitor/{id}', 'NodeController@nodeMonitor')->name('monitor'); // 节点流量监控
            Route::post('check/{id}', 'NodeController@checkNode')->name('check'); // 节点阻断检测
            Route::post('ping/{id}', 'NodeController@pingNode')->name('ping'); // 节点ping测速
            Route::get('pingLog', 'NodeController@pingLog')->name('pingLog'); // 节点Ping测速日志
            Route::get('refreshGeo/{id}', 'NodeController@refreshGeo')->name('geo'); // 更新节点
            Route::post('reload/{id}', 'NodeController@reload')->name('reload'); // 更新节点

            Route::prefix('auth')->name('auth.')->group(function () {
                Route::get('/', 'NodeController@authList')->name('index');
                Route::post('/', 'NodeController@addAuth')->name('store');
                Route::delete('{id}', 'NodeController@delAuth')->name('destroy');
                Route::put('{id}', 'NodeController@refreshAuth')->name('update');
            }); // 节点Api授权相关

            Route::resource('cert', 'CertController')->except('show'); // 节点域名tls相关
        });

        Route::resource('rule', 'RuleController')->except('create', 'edit', 'show');// 节点审计规则管理
        Route::name('rule.')->prefix('rule')->group(function () {
            Route::resource('group', 'RuleGroupController')->except('show');
            Route::name('group.')->prefix('group')->group(function () {
                Route::get('{id}/assign', 'RuleGroupController@assignNode')->name('editNode');
                Route::put('{id}/assign', 'RuleGroupController@assign')->name('assign'); // 规则分组关联节点
            });
            Route::get('log', 'RuleController@ruleLogList')->name('log'); // 用户触发审计规则日志
            Route::post('clear', 'RuleController@clearLog')->name('clear'); // 清除所有审计触发日志
        });

        Route::resource('goods', 'ShopController')->except('show');// 商品管理
        Route::resource('coupon', 'CouponController')->except('show', 'edit', 'update'); // 优惠券
        Route::get('coupon/export', 'CouponController@exportCoupon')->name('coupon.export'); // 导出优惠券

        Route::prefix('aff')->name('aff.')->group(function () {
            Route::get('/', 'AffiliateController@index')->name('index'); // 提现申请列表
            Route::get('detail/{id}', 'AffiliateController@detail')->name('detail'); // 提现申请详情
            Route::post('set', 'AffiliateController@setStatus')->name('setStatus'); // 设置提现申请状态
            Route::get('rebate', 'AffiliateController@rebate')->name('rebate'); // 返利流水记录
        });

        Route::get('order', 'LogsController@orderList')->name('order'); // 订单列表
        Route::prefix('log')->name('log.')->group(function () {
            Route::get('traffic', 'LogsController@trafficLog')->name('traffic'); // 流量日志
            Route::get('userCredit', 'LogsController@userCreditLogList')->name('credit'); // 余额变动记录
            Route::get('userTraffic', 'LogsController@userTrafficLogList')->name('flow'); // 流量变动记录
            Route::get('userBan', 'LogsController@userBanLogList')->name('ban'); // 用户封禁记录
            Route::get('userOnline', 'LogsController@userOnlineIPList')->name('ip'); // 用户在线IP记录
            Route::get("onlineIPMonitor", "LogsController@onlineIPMonitor")->name('online'); // 在线IP监控
            Route::get('notification', 'LogsController@notificationLog')->name('notify'); // 邮件发送日志
        });
        Route::get("payment/callbackList", "LogsController@callbackList")->name('payment.callback'); // 支付回调日志

        // 工具相关
        Route::prefix('tools')->name('tools.')->group(function () {
            Route::any("decompile", "ToolsController@decompile")->name('decompile'); // SS(R)链接反解析
            Route::get('download', 'ToolsController@download')->name('download'); // 下载转换过的JSON配置
            Route::any('convert', 'ToolsController@convert')->name('convert'); // 格式转换
            Route::any('import', 'ToolsController@import')->name('import'); // 数据导入
            Route::get('analysis', 'ToolsController@analysis')->name('analysis'); // 日志分析
        });

        Route::prefix('config')->name('config.')->namespace('Config')->group(function () {
            Route::resource('country', 'CountryController')->only('store', 'update', 'destroy'); // 等级配置
            Route::resource('filter', 'EmailFilterController')->only('index', 'store', 'destroy'); // 邮箱过滤
            Route::resource('label', 'LabelController')->only('store', 'update', 'destroy'); // 标签配置
            Route::resource('level', 'LevelController')->only('store', 'update', 'destroy'); // 等级配置
            Route::resource('ss', 'SsConfigController')->only('store', 'update', 'destroy'); // ss配置
        });

        Route::get('system', 'SystemController@index')->name('system'); // 系统设置
        Route::post('setExtend', 'SystemController@setExtend')->name('system.extend'); // 设置客服、统计代码
        Route::post('setConfig', 'SystemController@setConfig')->name('system.update'); // 设置某个配置项
        Route::post('sendTestNotification', 'SystemController@sendTestNotification')->name('test.notify'); //推送通知测试
        Route::get('epayInfo', 'Gateway\EPay@queryInfo')->name('test.epay');// 易支付信息
    });

    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->name('log.viewer'); // 系统运行日志
});
