<?php

Route::group(['namespace' => 'Api'], function(){
	// 定制客户端
	Route::any('login', 'LoginController@login');

	// PING检测
	Route::get('ping', 'PingController@ping');
});
