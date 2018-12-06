<?php

Route::group(['namespace' => 'Api'], function () {
    Route::any('yzy/create', 'YzyController@create');
    Route::resource('yzy', 'YzyController');
    Route::resource('trimepay', 'TrimepayController');

    // 定制客户端
    Route::any('login', 'LoginController@login');

    // PING检测
    Route::get('ping', 'PingController@ping');
});
