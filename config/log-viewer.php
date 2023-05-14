<?php

use Arcanedev\LogViewer\Contracts\Utilities\Filesystem;

return [

    /* -----------------------------------------------------------------
     |  Log files storage path
     | -----------------------------------------------------------------
     */

    'storage-path' => storage_path('logs'),

    /* -----------------------------------------------------------------
     |  Log files pattern
     | -----------------------------------------------------------------
     */

    'pattern' => [
        'prefix' => Filesystem::PATTERN_PREFIX,    // 'laravel-'
        'date' => Filesystem::PATTERN_DATE,      // '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]'
        'extension' => Filesystem::PATTERN_EXTENSION, // '.log'
    ],

    /* -----------------------------------------------------------------
     |  Locale
     | -----------------------------------------------------------------
     |  Supported locales :
     |    'auto', 'ar', 'bg', 'de', 'en', 'es', 'et', 'fa', 'fr', 'hu', 'hy', 'id', 'it', 'ja', 'ko', 'nl',
     |    'pl', 'pt-BR', 'ro', 'ru', 'sv', 'th', 'tr', 'zh-TW', 'zh'
     */

    'locale' => 'auto',

    /* -----------------------------------------------------------------
     |  Theme
     | -----------------------------------------------------------------
     |  Supported themes :
     |    'bootstrap-3', 'bootstrap-4'
     |  Make your own theme by adding a folder to the views directory and specifying it here.
     */

    'theme' => 'remark',

    /* -----------------------------------------------------------------
     |  Route settings
     | -----------------------------------------------------------------
     */

    'route' => [
        'enabled' => true,

        'attributes' => [
            'prefix' => 'admin/log-viewer',
            'middleware' => env('ARCANEDEV_LOGVIEWER_MIDDLEWARE') ? explode(',', env('ARCANEDEV_LOGVIEWER_MIDDLEWARE')) : ['web', 'admin'],
        ],
    ],

    /* -----------------------------------------------------------------
     |  Log entries per page
     | -----------------------------------------------------------------
     |  This defines how many logs & entries are displayed per page.
     */

    'per-page' => 30,

    /* -----------------------------------------------------------------
     |  Download settings
     | -----------------------------------------------------------------
     */

    'download' => [
        'prefix' => 'laravel-',

        'extension' => 'log',
    ],

    /* -----------------------------------------------------------------
     |  Menu settings
     | -----------------------------------------------------------------
     */

    'menu' => [
        'filter-route' => 'log-viewer::logs.filter',

        'icons-enabled' => true,
    ],

    /* -----------------------------------------------------------------
     |  Icons
     | -----------------------------------------------------------------
     */

    'icons' => [
        'all' => 'fa-solid fa-fw fa-list-ul',
        'emergency' => 'fa-solid fa-fw fa-life-ring',
        'alert' => 'fa-solid fa-fw fa-bullhorn',
        'critical' => 'fa-solid fa-fw fa-heart-pulse',
        'error' => 'fa-solid fa-fw fa-circle-xmark',
        'warning' => 'fa-solid fa-fw fa-triangle-exclamation',
        'notice' => 'fa-solid fa-fw fa-circle-exclamation',
        'info' => 'fa-solid fa-fw fa-circle-info',
        'debug' => 'fa-solid fa-fw fa-bug',
    ],

    /* -----------------------------------------------------------------
     |  Colors
     | -----------------------------------------------------------------
     */

    'colors' => [
        'levels' => [
            'empty' => '#D1D1D1',
            'all' => '#8A8A8A',
            'emergency' => '#E62020',
            'alert' => '#FF4C52',
            'critical' => '#FF666B',
            'error' => '#F57D1B',
            'warning' => '#FCB900',
            'notice' => '#589FFC',
            'info' => '#28C0DE',
            'debug' => '#526069',
        ],
    ],

    /* -----------------------------------------------------------------
     |  Strings to highlight in stack trace
     | -----------------------------------------------------------------
     */

    'highlight' => [
        '^#\d+',
        '^Stack trace:',
    ],

];
