<?php

use App\Http\Controllers\Api\Client\AuthController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\WebApi\CoreController;
use App\Http\Controllers\Api\WebApi\SSController;
use App\Http\Controllers\Api\WebApi\SSRController;
use App\Http\Controllers\Api\WebApi\TrojanController;
use App\Http\Controllers\Api\WebApi\V2RayController;

// 后端WEBAPI
Route::domain(sysConfig('web_api_url') ?: sysConfig('website_url'))->middleware('webApi')->group(function () {
    // 核心功能路由
    Route::prefix('{prefix}')->controller(CoreController::class)->group(function () {
        foreach ([
            Route::post('nodeStatus/{node}', 'setNodeStatus'), // 上报节点心跳信息
            Route::post('nodeOnline/{node}', 'setNodeOnline'), // 上报节点在线人数
            Route::post('userTraffic/{node}', 'setUserTraffic'), // 上报用户流量日志
            Route::get('nodeRule/{node}', 'getNodeRule'), // 获取节点的审计规则
            Route::post('trigger/{node}', 'addRuleLog'), // 上报用户触发的审计规则记录
        ] as $route) {
            $route->where('prefix', 'ss/v1|ssr/v1|web/v1|vnet/v2|v2ray/v1|trojan/v1');
        }
    });

    // SS后端WEBAPI V1版
    Route::prefix('ss/v1')->controller(SSController::class)->group(function () {
        Route::get('node/{node}', 'getNodeInfo'); // 获取节点信息
        Route::get('userList/{node}', 'getUserList'); // 获取节点可用的用户列表
    });

    // SSR后端WEBAPI V1版
    Route::prefix('{prefix}')->controller(SSRController::class)->group(function () {
        foreach ([
            Route::get('node/{node}', 'getNodeInfo'), // 获取节点信息
            Route::get('userList/{node}', 'getUserList'), // 获取节点可用的用户列表
        ] as $route) {
            $route->where('prefix', 'ssr/v1|web/v1|vnet/v2'); // SSR/VNet后端WEBAPI
        }
    });

    // V2Ray后端WEBAPI V1版
    Route::prefix('v2ray/v1')->controller(V2RayController::class)->group(function () {
        Route::get('node/{node}', 'getNodeInfo'); // 获取节点信息
        Route::get('userList/{node}', 'getUserList'); // 获取节点可用的用户列表
        Route::post('certificate/{node}', 'addCertificate'); // 上报节点伪装域名证书信息
    });

    // Trojan后端WEBAPI V1版
    Route::prefix('trojan/v1')->controller(TrojanController::class)->group(function () {
        Route::get('node/{node}', 'getNodeInfo'); // 获取节点信息
        Route::get('userList/{node}', 'getUserList'); // 获取节点可用的用户列表
    });
});

// 客户端API
Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login'); // 登录
        Route::post('register', 'register'); // 注册
        Route::get('logout', 'logout'); // 登出
    });

    Route::controller(ClientController::class)->group(function () {
        Route::get('getconfig', 'getConfig'); // 获取配置文件
        Route::get('version/update', 'checkClientVersion'); // 检查更新
        Route::get('shop', 'shop'); // 获取商品列表

        // 用户验证
        Route::middleware('auth.client')->group(function () {
            Route::get('getclash', 'downloadProxies'); // 下载节点配置
            Route::get('getuserinfo', 'getUserInfo'); // 获取用户信息
            Route::post('doCheckIn', 'checkIn'); // 签到
            Route::get('checkClashUpdate', 'proxyCheck'); // 判断是否更新订阅
            Route::get('proxy', 'getProxyList'); // 获取节点列表
            Route::get('tickets', 'ticketList'); // 获取工单列表
            Route::post('tickets/add', 'ticket_add'); // 提交工单
            Route::get('order', 'getOrders'); // 获取订单列表
            Route::get('invite/gift', 'getInvite'); // 获取邀请详情和列表
            Route::get('gettransfer', 'getUserTransfer'); // 获取剩余流量
        });
    });
});
