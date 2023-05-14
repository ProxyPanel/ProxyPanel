<?php

// 如何使用请参考 https://proxypanel.gitbook.io/wiki/page-modify#theme
return [
    'sidebar' => env('THEME_SIDEBAR', 'site-menubar-light'),
    'navbar' => [
        'inverse' => env('THEME_NAVBAR_INVERSE', 'navbar-inverse'),
        'skin' => env('THEME_NAVBAR_SKIN', 'bg-indigo-600'),
    ],
    'skin' => env('THEME_SKIN'),
];
