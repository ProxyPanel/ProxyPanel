<?php

Route::group(['namespace' => 'Api'], function () {
    Route::any('yzy/create', 'YzyController@create');
    Route::resource('yzy', 'YzyController');

    Route::get('login', 'LoginController@login');
});
