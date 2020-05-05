<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Debugbar Settings
     |--------------------------------------------------------------------------
     |
     | Debugbar is enabled by default, when debug is set to true in app.php.
     | You can override the value by setting enable to true or false instead of null.
     |
     | You can provide an array of URI's that must be ignored (eg. 'api/*')
     |
     */

    'enabled' => env('DEBUGBAR_ENABLED', NULL),
    'except' => [
        'telescope*'
    ],

    /*
     |--------------------------------------------------------------------------
     | Storage settings
     |--------------------------------------------------------------------------
     |
     | DebugBar stores data for session/ajax requests.
     | You can disable this, so the debugbar stores data in headers/session,
     | but this can cause problems with large data collectors.
     | By default, file storage (in the storage folder) is used. Redis and PDO
     | can also be used. For PDO, run the package migrations first.
     |
     */
    'storage' => [
        'enabled' => TRUE,
        'driver' => 'file', // redis, file, pdo, custom
        'path' => storage_path('debugbar'), // For file driver
        'connection' => NULL,   // Leave null for default connection (Redis/PDO)
        'provider' => '' // Instance of StorageInterface for custom driver
    ],

    /*
     |--------------------------------------------------------------------------
     | Vendors
     |--------------------------------------------------------------------------
     |
     | Vendor files are included by default, but can be set to false.
     | This can also be set to 'js' or 'css', to only include javascript or css vendor files.
     | Vendor files are for css: font-awesome (including fonts) and highlight.js (css files)
     | and for js: jquery and and highlight.js
     | So if you want syntax highlighting, set it to true.
     | jQuery is set to not conflict with existing jQuery scripts.
     |
     */

    'include_vendors' => TRUE,

    /*
     |--------------------------------------------------------------------------
     | Capture Ajax Requests
     |--------------------------------------------------------------------------
     |
     | The Debugbar can capture Ajax requests and display them. If you don't want this (ie. because of errors),
     | you can use this option to disable sending the data through the headers.
     |
     | Optionally, you can also send ServerTiming headers on ajax requests for the Chrome DevTools.
     */

    'capture_ajax' => TRUE,
    'add_ajax_timing' => FALSE,

    /*
     |--------------------------------------------------------------------------
     | Custom Error Handler for Deprecated warnings
     |--------------------------------------------------------------------------
     |
     | When enabled, the Debugbar shows deprecated warnings for Symfony components
     | in the Messages tab.
     |
     */
    'error_handler' => FALSE,

    /*
     |--------------------------------------------------------------------------
     | Clockwork integration
     |--------------------------------------------------------------------------
     |
     | The Debugbar can emulate the Clockwork headers, so you can use the Chrome
     | Extension, without the server-side code. It uses Debugbar collectors instead.
     |
     */
    'clockwork' => FALSE,

    /*
     |--------------------------------------------------------------------------
     | DataCollectors
     |--------------------------------------------------------------------------
     |
     | Enable/disable DataCollectors
     |
     */

    'collectors' => [
        'phpinfo' => TRUE,  // Php version
        'messages' => TRUE,  // Messages
        'time' => TRUE,  // Time Datalogger
        'memory' => TRUE,  // Memory usage
        'exceptions' => TRUE,  // Exception displayer
        'log' => TRUE,  // Logs from Monolog (merged in messages if enabled)
        'db' => TRUE,  // Show database (PDO) queries and bindings
        'views' => TRUE,  // Views with their data
        'route' => TRUE,  // Current route information
        'auth' => FALSE, // Display Laravel authentication status
        'gate' => TRUE, // Display Laravel Gate checks
        'session' => TRUE,  // Display session data
        'symfony_request' => TRUE,  // Only one can be enabled..
        'mail' => TRUE,  // Catch mail messages
        'laravel' => FALSE, // Laravel version and environment
        'events' => FALSE, // All events fired
        'default_request' => FALSE, // Regular or special Symfony request logger
        'logs' => FALSE, // Add the latest log messages
        'files' => FALSE, // Show the included files
        'config' => FALSE, // Display config settings
        'cache' => FALSE, // Display cache events
        'models' => FALSE, // Display models
    ],

    /*
     |--------------------------------------------------------------------------
     | Extra options
     |--------------------------------------------------------------------------
     |
     | Configure some DataCollectors
     |
     */

    'options' => [
        'auth' => [
            'show_name' => TRUE,   // Also show the users name/email in the debugbar
        ],
        'db' => [
            'with_params' => TRUE,   // Render SQL with the parameters substituted
            'backtrace' => TRUE,   // Use a backtrace to find the origin of the query in your files.
            'timeline' => FALSE,  // Add the queries to the timeline
            'explain' => [                 // Show EXPLAIN output on queries
                'enabled' => FALSE,
                'types' => ['SELECT'],     // // workaround ['SELECT'] only. https://github.com/barryvdh/laravel-debugbar/issues/888 ['SELECT', 'INSERT', 'UPDATE', 'DELETE']; for MySQL 5.6.3+
            ],
            'hints' => TRUE,    // Show hints for common mistakes
        ],
        'mail' => [
            'full_log' => FALSE
        ],
        'views' => [
            'data' => FALSE,    //Note: Can slow down the application, because the data can be quite large..
        ],
        'route' => [
            'label' => TRUE  // show complete route on bar
        ],
        'logs' => [
            'file' => NULL
        ],
        'cache' => [
            'values' => TRUE // collect cache values
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Inject Debugbar in Response
     |--------------------------------------------------------------------------
     |
     | Usually, the debugbar is added just before </body>, by listening to the
     | Response after the App is done. If you disable this, you have to add them
     | in your template yourself. See http://phpdebugbar.com/docs/rendering.html
     |
     */

    'inject' => TRUE,

    /*
     |--------------------------------------------------------------------------
     | DebugBar route prefix
     |--------------------------------------------------------------------------
     |
     | Sometimes you want to set route prefix to be used by DebugBar to load
     | its resources from. Usually the need comes from misconfigured web server or
     | from trying to overcome bugs like this: http://trac.nginx.org/nginx/ticket/97
     |
     */
    'route_prefix' => '_debugbar',

    /*
     |--------------------------------------------------------------------------
     | DebugBar route domain
     |--------------------------------------------------------------------------
     |
     | By default DebugBar route served from the same domain that request served.
     | To override default domain, specify it as a non-empty value.
     */
    'route_domain' => NULL,
];
