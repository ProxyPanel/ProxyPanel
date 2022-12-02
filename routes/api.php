<?php

// 后端WEBAPI
Route::group(['namespace' => 'Api\WebApi', 'middleware' => 'webApi', 'domain' => sysConfig('web_api_url') ?: sysConfig('website_url')], function () {
    // ss后端WEBAPI V1版
    Route::group(['prefix' => 'ss/v1'], function () {
        Route::get('node/{node}', 'SSController@getNodeInfo'); // 获取节点信息
        Route::post('nodeStatus/{node}', 'CoreController@setNodeStatus'); // 上报节点心跳信息
        Route::post('nodeOnline/{node}', 'CoreController@setNodeOnline'); // 上报节点在线人数
        Route::get('userList/{node}', 'SSController@getUserList'); // 获取节点可用的用户列表
        Route::post('userTraffic/{node}', 'CoreController@setUserTraffic'); // 上报用户流量日志
        Route::get('nodeRule/{node}', 'CoreController@getNodeRule'); // 获取节点的审计规则
        Route::post('trigger/{node}', 'CoreController@addRuleLog'); // 上报用户触发的审计规则记录
    });

    // SSR后端WEBAPI V1版
    Route::group(['prefix' => 'ssr/v1'], function () {
        Route::get('node/{node}', 'SSRController@getNodeInfo'); // 获取节点信息
        Route::post('nodeStatus/{node}', 'CoreController@setNodeStatus'); // 上报节点心跳信息
        Route::post('nodeOnline/{node}', 'CoreController@setNodeOnline'); // 上报节点在线人数
        Route::get('userList/{node}', 'SSRController@getUserList'); // 获取节点可用的用户列表
        Route::post('userTraffic/{node}', 'CoreController@setUserTraffic'); // 上报用户流量日志
        Route::get('nodeRule/{node}', 'CoreController@getNodeRule'); // 获取节点的审计规则
        Route::post('trigger/{node}', 'CoreController@addRuleLog'); // 上报用户触发的审计规则记录
    });

    // VNet后端WEBAPI V1版 !!! 即将遗弃的api
    Route::group(['prefix' => 'web/v1'], function () {
        Route::get('node/{node}', 'SSRController@getNodeInfo'); // 获取节点信息
        Route::post('nodeStatus/{node}', 'CoreController@setNodeStatus'); // 上报节点心跳信息
        Route::post('nodeOnline/{node}', 'CoreController@setNodeOnline'); // 上报节点在线人数
        Route::get('userList/{node}', 'SSRController@getUserList'); // 获取节点可用的用户列表
        Route::post('userTraffic/{node}', 'CoreController@setUserTraffic'); // 上报用户流量日志
        Route::get('nodeRule/{node}', 'CoreController@getNodeRule'); // 获取节点的审计规则
        Route::post('trigger/{node}', 'CoreController@addRuleLog'); // 上报用户触发的审计规则记录
    });

    // VNet后端WEBAPI V2版 !!! 即将遗弃的api
    Route::group(['prefix' => 'vnet/v2'], function () {
        Route::get('node/{node}', 'SSRController@getNodeInfo'); // 获取节点信息
        Route::post('nodeStatus/{node}', 'CoreController@setNodeStatus'); // 上报节点心跳信息
        Route::post('nodeOnline/{node}', 'CoreController@setNodeOnline'); // 上报节点在线人数
        Route::get('userList/{node}', 'SSRController@getUserList'); // 获取节点可用的用户列表
        Route::post('userTraffic/{node}', 'CoreController@setUserTraffic'); // 上报用户流量日志
        Route::get('nodeRule/{node}', 'CoreController@getNodeRule'); // 获取节点的审计规则
        Route::post('trigger/{node}', 'CoreController@addRuleLog'); // 上报用户触发的审计规则记录
    });

    // V2Ray后端WEBAPI V1版
    Route::group(['prefix' => 'v2ray/v1'], function () {
        Route::get('node/{node}', 'V2RayController@getNodeInfo'); // 获取节点信息
        Route::post('nodeStatus/{node}', 'CoreController@setNodeStatus'); // 上报节点心跳信息
        Route::post('nodeOnline/{node}', 'CoreController@setNodeOnline'); // 上报节点在线人数
        Route::get('userList/{node}', 'V2RayController@getUserList'); // 获取节点可用的用户列表
        Route::post('userTraffic/{node}', 'CoreController@setUserTraffic'); // 上报用户流量日志
        Route::get('nodeRule/{node}', 'CoreController@getNodeRule'); // 获取节点的审计规则
        Route::post('trigger/{node}', 'CoreController@addRuleLog'); // 上报用户触发的审计规则记录
        Route::post('certificate/{node}', 'V2RayController@addCertificate'); // 上报节点伪装域名证书信息
    });

    // Trojan后端WEBAPI V1版
    Route::group(['prefix' => 'trojan/v1'], function () {
        Route::get('node/{node}', 'TrojanController@getNodeInfo'); // 获取节点信息
        Route::post('nodeStatus/{node}', 'CoreController@setNodeStatus'); // 上报节点心跳信息
        Route::post('nodeOnline/{node}', 'CoreController@setNodeOnline'); // 上报节点在线人数
        Route::get('userList/{node}', 'TrojanController@getUserList'); // 获取节点可用的用户列表
        Route::post('userTraffic/{node}', 'CoreController@setUserTraffic'); // 上报用户流量日志
        Route::get('nodeRule/{node}', 'CoreController@getNodeRule'); // 获取节点的审计规则
        Route::post('trigger/{node}', 'CoreController@addRuleLog'); // 上报用户触发的审计规则记录
    });
});

// 客户端API

Route::group(['namespace' => 'Api\Client', 'prefix' => 'v1'], function () {
    Route::post('login', 'AuthController@login'); // 登录
    Route::post('register', 'AuthController@register'); // 注册
    Route::get('logout', 'AuthController@logout'); //登出
    Route::get('getconfig', 'ClientController@getConfig'); // 获取配置文件
    Route::get('version/update', 'ClientController@checkClientVersion'); // 检查更新
    Route::get('shop', 'ClientController@shop'); // 获取商品列表
    Route::group(['middleware' => 'auth.client'], function () { // 用户验证
        Route::get('getclash', 'ClientController@downloadProxies'); // 下载节点配置
        Route::get('getuserinfo', 'ClientController@getUserInfo'); // 获取用户信息
        Route::post('doCheckIn', 'ClientController@checkIn'); // 签到
        Route::get('checkClashUpdate', 'ClientController@proxyCheck'); // 判断是否更新订阅
        Route::get('proxy', 'ClientController@getProxyList'); // 获取节点列表
        Route::get('tickets', 'ClientController@ticketList'); // 获取工单列表
        Route::post('tickets/add', 'ClientController@ticket_add'); // 提交工单
        Route::get('order', 'ClientController@getOrders'); // 获取订单列表
        Route::get('invite/gift', 'ClientController@getInvite'); // 获取邀请详情和列表
        Route::get('gettransfer', 'ClientController@getUserTransfer'); // 获取剩余流量
    });
});
