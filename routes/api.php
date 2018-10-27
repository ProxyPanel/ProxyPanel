<?php

Route::group(['namespace' => 'Api'], function () {
    Route::any('yzy/create', 'YzyController@create');
    Route::resource('yzy', 'YzyController');

    // 定制客户端
    Route::get('login', 'LoginController@login');

    // PING检测
    Route::get('ping', 'PingController@ping');
});
