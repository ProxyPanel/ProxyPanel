<?php

Route::group(['namespace' => 'Api'], function(){
	Route::resource('alipay', 'AlipayController');
	Route::resource('f2fpay', 'F2fpayController');
	Route::resource('payjs','PayJsController');

	// 定制客户端
	Route::any('login', 'LoginController@login');

	// PING检测
	Route::get('ping', 'PingController@ping');
});
