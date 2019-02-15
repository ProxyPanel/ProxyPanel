<?php

Route::group(['namespace' => 'Api'], function () {
    Route::resource('yzy', 'YzyController');
    Route::resource('alipay', 'AlipayController');
    Route::resource('f2fpay', 'F2fpayController');

    // 定制客户端
    Route::any('login', 'LoginController@login');

    // PING检测
    Route::get('ping', 'PingController@ping');
});
