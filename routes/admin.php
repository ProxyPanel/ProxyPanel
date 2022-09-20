<?php

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', 'AdminController@index')->name('index'); // 后台首页
    Route::get('config', 'AdminController@config')->name('config'); // 系统设置
    Route::get('invite', 'AdminController@inviteList')->name('invite.index'); // 邀请码列表
    Route::post('invite', 'AdminController@makeInvite')->name('invite.create'); // 生成邀请码
    Route::get('Invite/export', 'AdminController@exportInvite')->name('invite.export'); // 导出邀请码
    Route::get('epayInfo', 'Gateway\EPay@queryInfo')->name('test.epay'); // 易支付信息

    Route::namespace('Admin')->group(function () {
        Route::resource('user', 'UserController')->except('show');
        Route::name('user.')->group(function () {
            Route::get('oauth', 'UserController@oauth')->name('oauth'); // 第三方登录信息
            Route::post('batchAdd', 'UserController@batchAddUsers')->name('batch'); // 批量生成账号
            Route::resource('group', 'UserGroupController')->except('show'); // 用户分组管理
            Route::get('monitor/{user}', 'LogsController@userTrafficMonitor')->name('monitor'); // 用户流量监控
            Route::get('online/{id}', 'LogsController@onlineIPMonitor')->name('online'); // 在线IP监控
            Route::post('switch/{user}', 'UserController@switchToUser')->name('switch'); // 转换成某个用户的身份
            Route::post('updateCredit/{user}', 'UserController@handleUserCredit')->name('updateCredit'); // 用户余额充值
            Route::post('reset/{user}', 'UserController@resetTraffic')->name('reset'); // 重置用户流量
            Route::get('export/{user}', 'UserController@export')->name('export'); // 查看配置信息
            Route::post('export/{user}', 'UserController@exportProxyConfig')->name('exportProxy'); // 读取配置信息
            Route::post('vnet/{user}', 'UserController@VNetInfo')->name('VNetInfo'); // VNet用户开通检测
        });

        Route::prefix('subscribe')->name('subscribe.')->group(function () {
            Route::get('/', 'SubscribeController@index')->name('index'); // 订阅码列表
            Route::get('log/{id}', 'SubscribeController@subscribeLog')->name('log'); // 订阅码记录
            Route::post('set/{subscribe}', 'SubscribeController@setSubscribeStatus')->name('set'); // 启用禁用用户的订阅
        });

        Route::resource('ticket', 'TicketController')->except('create', 'show');
        Route::resource('article', 'ArticleController');
        Route::prefix('marketing')->name('marketing.')->group(function () {
            Route::get('email', 'MarketingController@emailList')->name('email'); // 邮件消息列表
            Route::get('push', 'MarketingController@pushList')->name('push'); // 推送消息列表
            Route::post('add', 'MarketingController@addPushMarketing')->name('add'); // 推送消息
        });

        Route::resource('node', 'NodeController')->except('show');
        Route::prefix('node')->name('node.')->group(function () {
            Route::get('clone/{node}', 'NodeController@clone')->name('clone'); // 节点流量监控
            Route::get('monitor/{node}', 'NodeController@nodeMonitor')->name('monitor'); // 节点流量监控
            Route::post('check/{node}', 'NodeController@checkNode')->name('check'); // 节点阻断检测
            Route::post('ping/{node}', 'NodeController@pingNode')->name('ping'); // 节点ping测速
            Route::get('refreshGeo/{id}', 'NodeController@refreshGeo')->name('geo'); // 更新节点
            Route::post('reload/{id}', 'NodeController@reload')->name('reload'); // 更新节点

            Route::resource('auth', 'NodeAuthController')->except(['create', 'show', 'edit']); // 节点授权相关
            Route::resource('cert', 'CertController')->except('show'); // 节点域名tls相关
        });

        Route::resource('rule', 'RuleController')->except('create', 'edit', 'show'); // 节点审计规则管理
        Route::name('rule.')->prefix('rule')->group(function () {
            Route::resource('group', 'RuleGroupController')->except('show');
            Route::get('log', 'RuleController@ruleLogList')->name('log'); // 用户触发审计规则日志
            Route::post('clear', 'RuleController@clearLog')->name('clear'); // 清除所有审计触发日志
        });

        Route::resource('goods', 'ShopController')->except('show'); // 商品管理
        Route::resource('coupon', 'CouponController')->except('show', 'edit', 'update'); // 优惠券
        Route::get('coupon/export', 'CouponController@exportCoupon')->name('coupon.export'); // 导出优惠券

        Route::prefix('aff')->name('aff.')->group(function () {
            Route::get('/', 'AffiliateController@index')->name('index'); // 提现申请列表
            Route::get('rebate', 'AffiliateController@rebate')->name('rebate'); // 返利流水记录
            Route::get('/{aff}', 'AffiliateController@detail')->name('detail'); // 提现申请详情
            Route::put('/{aff}', 'AffiliateController@setStatus')->name('setStatus'); // 设置提现申请状态
        });

        Route::get('order', 'LogsController@orderList')->name('order'); // 订单列表
        Route::post('order/edit', 'LogsController@changeOrderStatus')->name('order.edit'); // 订单列表

        Route::prefix('report')->name('report.')->group(function () {
            Route::get('accounting', 'ReportController@accounting')->name('accounting'); // 流水账簿
            Route::get('user/analysis', 'ReportController@userAnalysis')->name('userAnalysis'); // 用户流量分析
        });

        Route::prefix('log')->name('log.')->group(function () {
            Route::get('traffic', 'LogsController@trafficLog')->name('traffic'); // 流量日志
            Route::get('userCredit', 'LogsController@userCreditLogList')->name('credit'); // 余额变动记录
            Route::get('userTraffic', 'LogsController@userTrafficLogList')->name('flow'); // 流量变动记录
            Route::get('userBan', 'LogsController@userBanLogList')->name('ban'); // 用户封禁记录
            Route::get('userOnline', 'LogsController@userOnlineIPList')->name('ip'); // 用户在线IP记录
            Route::get('onlineIPMonitor', 'LogsController@onlineIPMonitor')->name('online'); // 在线IP监控
            Route::get('notification', 'LogsController@notificationLog')->name('notify'); // 邮件发送日志
        });
        Route::get('payment/callbackList', 'LogsController@callbackList')->name('payment.callback'); // 支付回调日志

        // 工具相关
        Route::prefix('tools')->name('tools.')->group(function () {
            Route::match(['get', 'post'], 'decompile', 'ToolsController@decompile')->name('decompile'); // SS(R)链接反解析
            Route::get('download', 'ToolsController@download')->name('download'); // 下载转换过的JSON配置
            Route::match(['get', 'post'], 'convert', 'ToolsController@convert')->name('convert'); // 格式转换
            Route::match(['get', 'post'], 'import', 'ToolsController@import')->name('import'); // 数据导入
            Route::get('analysis', 'ToolsController@analysis')->name('analysis'); // 日志分析
        });

        Route::prefix('config')->name('config.')->namespace('Config')->group(function () {
            Route::resource('country', 'CountryController')->only('store', 'update', 'destroy'); // 等级配置
            Route::resource('filter', 'EmailFilterController')->only('index', 'store', 'destroy'); // 邮箱过滤
            Route::resource('label', 'LabelController')->only('store', 'update', 'destroy'); // 标签配置
            Route::resource('level', 'LevelController')->only('store', 'update', 'destroy'); // 等级配置
            Route::resource('ss', 'SsConfigController')->only('store', 'update', 'destroy'); // ss配置
            Route::resource('category', 'CategoryController')->only('store', 'update', 'destroy'); // 商品分类配置
        });

        Route::resource('permission', 'PermissionController')->except('show');
        Route::resource('role', 'RoleController')->except('show');

        Route::get('system', 'SystemController@index')->name('system.index'); // 系统设置
        Route::post('setExtend', 'SystemController@setExtend')->name('system.extend'); // 设置logo图片文件
        Route::post('setConfig', 'SystemController@setConfig')->name('system.update'); // 设置某个配置项
        Route::post('sendTestNotification', 'SystemController@sendTestNotification')->name('test.notify'); //推送通知测试
    });
});
