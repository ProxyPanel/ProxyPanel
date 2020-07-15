<?php
// 后端WEBAPI
Route::group(['namespace' => 'Api\WebApi', 'middleware' => ['webApi']], function() {
	// VNet后端WEBAPI V1版
	Route::group(['prefix' => 'web/v1'], function() {
		Route::get('node/{id}', 'VNetController@getNodeInfo'); // 获取节点信息
		Route::post('nodeStatus/{id}', 'BaseController@setNodeStatus'); // 上报节点心跳信息
		Route::post('nodeOnline/{id}', 'BaseController@setNodeOnline'); // 上报节点在线人数
		Route::get('userList/{id}', 'VNetController@getUserList'); // 获取节点可用的用户列表
		Route::post('userTraffic/{id}', 'BaseController@setUserTraffic'); // 上报用户流量日志
		Route::get('nodeRule/{id}', 'BaseController@getNodeRule'); // 获取节点的审计规则
		Route::post('trigger/{id}', 'BaseController@addRuleLog'); // 上报用户触发的审计规则记录
	});

	// VNet后端WEBAPI V2版
	Route::group(['prefix' => 'vnet/v2'], function() {
		Route::get('node/{id}', 'VNetController@getNodeInfo'); // 获取节点信息
		Route::post('nodeStatus/{id}', 'BaseController@setNodeStatus'); // 上报节点心跳信息
		Route::post('nodeOnline/{id}', 'BaseController@setNodeOnline'); // 上报节点在线人数
		Route::get('userList/{id}', 'VNetController@getUserList'); // 获取节点可用的用户列表
		Route::post('userTraffic/{id}', 'BaseController@setUserTraffic'); // 上报用户流量日志
		Route::get('nodeRule/{id}', 'BaseController@getNodeRule'); // 获取节点的审计规则
		Route::post('trigger/{id}', 'BaseController@addRuleLog'); // 上报用户触发的审计规则记录
	});

	// V2Ray后端WEBAPI V1版
	Route::group(['prefix' => 'v2ray/v1'], function() {
		Route::get('node/{id}', 'V2RayController@getNodeInfo'); // 获取节点信息
		Route::post('nodeStatus/{id}', 'BaseController@setNodeStatus'); // 上报节点心跳信息
		Route::post('nodeOnline/{id}', 'BaseController@setNodeOnline'); // 上报节点在线人数
		Route::get('userList/{id}', 'V2RayController@getUserList'); // 获取节点可用的用户列表
		Route::post('userTraffic/{id}', 'BaseController@setUserTraffic'); // 上报用户流量日志
		Route::get('nodeRule/{id}', 'BaseController@getNodeRule'); // 获取节点的审计规则
		Route::post('trigger/{id}', 'BaseController@addRuleLog'); // 上报用户触发的审计规则记录
		Route::post('certificate/{id}', 'V2RayController@addCertificate'); // 上报节点伪装域名证书信息
	});

	// Trojan后端WEBAPI V1版
	Route::group(['prefix' => 'trojan/v1'], function() {
		Route::get('node/{id}', 'TrojanController@getNodeInfo'); // 获取节点信息
		Route::post('nodeStatus/{id}', 'BaseController@setNodeStatus'); // 上报节点心跳信息
		Route::post('nodeOnline/{id}', 'BaseController@setNodeOnline'); // 上报节点在线人数
		Route::get('userList/{id}', 'TrojanController@getUserList'); // 获取节点可用的用户列表
		Route::post('userTraffic/{id}', 'BaseController@setUserTraffic'); // 上报用户流量日志
		Route::get('nodeRule/{id}', 'BaseController@getNodeRule'); // 获取节点的审计规则
		Route::post('trigger/{id}', 'BaseController@addRuleLog'); // 上报用户触发的审计规则记录
	});

});
