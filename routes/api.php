<?php

// V2Ray后端WEBAPI V1版
Route::group(['namespace' => 'Api\V2Ray', 'middleware' => ['webApi'], 'prefix' => 'v2ray/v1'], function () {
	Route::get('node/{id}', 'V1Controller@getNodeInfo'); // 获取节点信息
	Route::post('nodeStatus/{id}', 'V1Controller@setNodeStatus'); // 上报节点心跳信息
	Route::post('nodeOnline/{id}', 'V1Controller@setNodeOnline'); // 上报节点在线人数
	Route::get('userList/{id}', 'V1Controller@getUserList'); // 获取节点可用的用户列表
	Route::post('userTraffic/{id}', 'V1Controller@setUserTraffic'); // 上报用户流量日志
	Route::get('nodeRule/{id}', 'V1Controller@getNodeRule'); // 获取节点的审计规则
	Route::post('trigger/{id}', 'V1Controller@addRuleLog'); // 上报用户触发的审计规则记录
	Route::post('certificate/{id}', 'V1Controller@addCertificate'); // 上报节点伪装域名证书信息
});

// Trojan后端WEBAPI V1版
Route::group(['namespace' => 'Api\Trojan', 'middleware' => ['webApi'], 'prefix' => 'trojan/v1'], function () {
	Route::get('node/{id}', 'V1Controller@getNodeInfo'); // 获取节点信息
	Route::post('nodeStatus/{id}', 'V1Controller@setNodeStatus'); // 上报节点心跳信息
	Route::post('nodeOnline/{id}', 'V1Controller@setNodeOnline'); // 上报节点在线人数
	Route::get('userList/{id}', 'V1Controller@getUserList'); // 获取节点可用的用户列表
	Route::post('userTraffic/{id}', 'V1Controller@setUserTraffic'); // 上报用户流量日志
	Route::get('nodeRule/{id}', 'V1Controller@getNodeRule'); // 获取节点的审计规则
	Route::post('trigger/{id}', 'V1Controller@addRuleLog'); // 上报用户触发的审计规则记录
});
