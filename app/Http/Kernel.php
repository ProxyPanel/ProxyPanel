<?php

namespace App\Http;

use App\Http\Middleware\AcceptHeader;
use App\Http\Middleware\Affiliate;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\ClientAuthenticate;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\isForbidden;
use App\Http\Middleware\isLogin;
use App\Http\Middleware\isMaintenance;
use App\Http\Middleware\isSecurity;
use App\Http\Middleware\Permission;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\Telegram;
use App\Http\Middleware\tmpCORS;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\WebApi;
use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        HandleCors::class,
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            SetLocale::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ],

        'user' => [
            isForbidden::class,
            isMaintenance::class,
            isLogin::class,
        ],

        'admin' => [
            isForbidden::class,
            isLogin::class,
            Permission::class,
        ],

        'api' => [
            AcceptHeader::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            tmpCORS::class,
            SetLocale::class,
            'throttle:60,1',
            SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'auth.client' => ClientAuthenticate::class,
        'bindings' => SubstituteBindings::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'signed' => ValidateSignature::class,
        'telegram' => Telegram::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'webApi' => WebApi::class,
        'isMaintenance' => isMaintenance::class,
        'isSecurity' => isSecurity::class,
        'isForbidden' => isForbidden::class,
        'affiliate' => Affiliate::class,
    ];
}
