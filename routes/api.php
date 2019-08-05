<?php

Route::group(['namespace' => 'Api'], function () {
    Route::resource('yzy', 'YzyController');
    Route::resource('alipay', 'AlipayController');
    Route::resource('f2fpay', 'F2fpayController');
});
