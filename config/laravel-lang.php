<?php

return [

    /* -----------------------------------------------------------------
     |  The vendor path
     | -----------------------------------------------------------------
     */

    /** @link      https://github.com/caouecs/Laravel-lang */
    'vendor' => base_path('vendor/caouecs/laravel-lang/src'),

    /* -----------------------------------------------------------------
     |  Supported locales
     | -----------------------------------------------------------------
     | If you want to limit your translations, set your supported locales list.
     */

    'locales' => [
        'ko',
        'zh-CN',
    ],

    /* -----------------------------------------------------------------
     |  Check Settings
     | -----------------------------------------------------------------
     */

    'check' => [
        'ignore' => [
            'validation.custom',
            'validation.attributes',
        ],
    ],

];
