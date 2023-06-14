<?php

use App\Http\Controllers\Admin\AffiliateController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CertController;
use App\Http\Controllers\Admin\Config\CategoryController;
use App\Http\Controllers\Admin\Config\CountryController;
use App\Http\Controllers\Admin\Config\EmailFilterController;
use App\Http\Controllers\Admin\Config\LabelController;
use App\Http\Controllers\Admin\Config\LevelController;
use App\Http\Controllers\Admin\Config\SsConfigController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\LogsController;
use App\Http\Controllers\Admin\MarketingController;
use App\Http\Controllers\Admin\NodeAuthController;
use App\Http\Controllers\Admin\NodeController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RuleController;
use App\Http\Controllers\Admin\RuleGroupController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\SubscribeController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\ToolsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserGroupController;
use App\Http\Controllers\AdminController;
use App\Utils\Payments\EPay;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::controller(AdminController::class)->group(function () {
        Route::get('/', 'index')->name('index'); // 后台首页
        Route::get('config', 'config')->name('config.index'); // 系统通用配置
        Route::get('invite', 'inviteList')->name('invite.index'); // 邀请码列表
        Route::post('invite', 'makeInvite')->name('invite.create'); // 生成邀请码
        Route::get('Invite/export', 'exportInvite')->name('invite.export'); // 导出邀请码
    });
    Route::get('epayInfo', [EPay::class, 'queryInfo'])->name('test.epay'); // 易支付信息

    Route::resource('user', UserController::class)->except('show');
    Route::name('user.')->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('oauth', 'oauth')->name('oauth'); // 第三方登录信息
            Route::post('batchAdd', 'batchAddUsers')->name('batch'); // 批量生成账号
            Route::post('switch/{user}', 'switchToUser')->name('switch'); // 转换成某个用户的身份
            Route::post('updateCredit/{user}', 'handleUserCredit')->name('updateCredit'); // 用户余额充值
            Route::post('reset/{user}', 'resetTraffic')->name('reset'); // 重置用户流量
            Route::get('export/{user}', 'export')->name('export'); // 查看配置信息
            Route::post('export/{user}', 'exportProxyConfig')->name('exportProxy'); // 读取配置信息
            Route::post('vnet/{user}', 'VNetInfo')->name('VNetInfo'); // VNet用户开通检测
        });
        Route::resource('group', UserGroupController::class)->except('show'); // 用户分组管理
        Route::get('monitor/{user}', [LogsController::class, 'userTrafficMonitor'])->name('monitor'); // 用户流量监控
        Route::get('online/{id}', [LogsController::class, 'onlineIPMonitor'])->name('online'); // 在线IP监控
    });

    Route::prefix('subscribe')->name('subscribe.')->controller(SubscribeController::class)->group(function () {
        Route::get('/', 'index')->name('index'); // 订阅码列表
        Route::get('log/{id}', 'subscribeLog')->name('log'); // 订阅码记录
        Route::post('set/{subscribe}', 'setSubscribeStatus')->name('set'); // 启用禁用用户的订阅
    });

    Route::resource('ticket', TicketController::class)->except('create', 'show');
    Route::resource('article', ArticleController::class);
    Route::prefix('marketing')->name('marketing.')->controller(MarketingController::class)->group(function () {
        Route::get('email', 'emailList')->name('email'); // 邮件消息列表
        Route::get('push', 'pushList')->name('push'); // 推送消息列表
        Route::post('add', 'addPushMarketing')->name('add'); // 推送消息
    });

    Route::resource('node', NodeController::class)->except('show');
    Route::prefix('node')->name('node.')->controller(NodeController::class)->group(function () {
        Route::get('clone/{node}', 'clone')->name('clone'); // 节点流量监控
        Route::get('monitor/{node}', 'nodeMonitor')->name('monitor'); // 节点流量监控
        Route::post('check/{node}', 'checkNode')->name('check'); // 节点阻断检测
        Route::post('ping/{node}', 'pingNode')->name('ping'); // 节点ping测速
        Route::get('refreshGeo/{id}', 'refreshGeo')->name('geo'); // 更新节点
        Route::post('reload/{id}', 'reload')->name('reload'); // 更新节点
        Route::resource('auth', NodeAuthController::class)->except(['create', 'show', 'edit']); // 节点授权相关
        Route::resource('cert', CertController::class)->except('show'); // 节点域名tls相关
    });

    Route::resource('rule', RuleController::class)->except('create', 'edit', 'show'); // 节点审计规则管理
    Route::name('rule.')->prefix('rule')->group(function () {
        Route::resource('group', RuleGroupController::class)->except('show');
        Route::get('log', [RuleController::class, 'ruleLogList'])->name('log'); // 用户触发审计规则日志
        Route::post('clear', [RuleController::class, 'clearLog'])->name('clear'); // 清除所有审计触发日志
    });

    Route::resource('goods', ShopController::class)->except('show'); // 商品管理
    Route::get('coupon/export', [CouponController::class, 'exportCoupon'])->name('coupon.export'); // 导出优惠券
    Route::resource('coupon', CouponController::class)->except('edit', 'update'); // 优惠券

    Route::prefix('aff')->name('aff.')->controller(AffiliateController::class)->group(function () {
        Route::get('/', 'index')->name('index'); // 提现申请列表
        Route::get('rebate', 'rebate')->name('rebate'); // 返利流水记录
        Route::get('/{aff}', 'detail')->name('detail'); // 提现申请详情
        Route::put('/{aff}', 'setStatus')->name('setStatus'); // 设置提现申请状态
    });

    Route::controller(LogsController::class)->group(function () {
        Route::get('order', 'orderList')->name('order'); // 订单列表
        Route::post('order/edit', 'changeOrderStatus')->name('order.edit'); // 订单列表
    });
    Route::prefix('report')->name('report.')->controller(ReportController::class)->group(function () {
        Route::get('accounting', 'accounting')->name('accounting'); // 流水账簿
        Route::get('user/analysis', 'userAnalysis')->name('userAnalysis'); // 用户流量分析
    });

    Route::prefix('log')->name('log.')->controller(LogsController::class)->group(function () {
        Route::get('traffic', 'trafficLog')->name('traffic'); // 流量日志
        Route::get('userCredit', 'userCreditLogList')->name('credit'); // 余额变动记录
        Route::get('userTraffic', 'userTrafficLogList')->name('flow'); // 流量变动记录
        Route::get('userBan', 'userBanLogList')->name('ban'); // 用户封禁记录
        Route::get('userOnline', 'userOnlineIPList')->name('ip'); // 用户在线IP记录
        Route::get('onlineIPMonitor', 'onlineIPMonitor')->name('online'); // 在线IP监控
        Route::get('notification', 'notificationLog')->name('notify'); // 邮件发送日志
    });
    Route::get('payment/callbackList', [LogsController::class, 'callbackList'])->name('payment.callback'); // 支付回调日志

    // 工具相关
    Route::prefix('tools')->name('tools.')->controller(ToolsController::class)->group(function () {
        Route::match(['get', 'post'], 'decompile', 'decompile')->name('decompile'); // SS(R)链接反解析
        Route::get('download', 'download')->name('download'); // 下载转换过的JSON配置
        Route::match(['get', 'post'], 'convert', 'convert')->name('convert'); // 格式转换
        Route::match(['get', 'post'], 'import', 'import')->name('import'); // 数据导入
        Route::get('analysis', 'analysis')->name('analysis'); // 日志分析
    });

    Route::prefix('config')->name('config.')->group(function () {
        Route::resource('country', CountryController::class)->only('store', 'update', 'destroy'); // 等级配置
        Route::resource('filter', EmailFilterController::class)->only('index', 'store', 'destroy'); // 邮箱过滤
        Route::resource('label', LabelController::class)->only('store', 'update', 'destroy'); // 标签配置
        Route::resource('level', LevelController::class)->only('store', 'update', 'destroy'); // 等级配置
        Route::resource('ss', SsConfigController::class)->only('store', 'update', 'destroy'); // ss配置
        Route::resource('category', CategoryController::class)->only('store', 'update', 'destroy'); // 商品分类配置
    });

    Route::resource('permission', PermissionController::class)->except('show');
    Route::resource('role', RoleController::class)->except('show');

    Route::controller(SystemController::class)->group(function () {
        Route::get('system', 'index')->name('system.index'); // 系统设置
        Route::post('setExtend', 'setExtend')->name('system.extend'); // 设置logo图片文件
        Route::post('setConfig', 'setConfig')->name('system.update'); // 设置某个配置项
        Route::post('sendTestNotification', 'sendTestNotification')->name('test.notify'); //推送通知测试
    });
});
