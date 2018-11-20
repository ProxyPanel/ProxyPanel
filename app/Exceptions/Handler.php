<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use ReflectionException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  Exception $exception
     *
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (config('app.debug')) {
            \Log::info("请求导致异常的地址：" . $request->fullUrl() . "，请求IP：" . $request->getClientIp());

            parent::render($request, $exception);
        }

        // 捕获身份校验异常
        if ($exception instanceof AuthenticationException) {
            if ($request->ajax()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => 'Unauthorized']);
            } else {
                return response()->view('error.404');
            }
        }

        // 捕获CSRF异常
        if ($exception instanceof TokenMismatchException) {
            if ($request->ajax()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => trans('404.csrf_title')]);
            } else {
                return response()->view('error.csrf');
            }
        }

        if ($exception instanceof ReflectionException) {
            if ($request->ajax()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => 'System Error']);
            } else {
                return response()->view('error.404');
            }
        }

        return response()->view('error.404');
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request                 $request
     * @param  \Illuminate\Auth\AuthenticationException $exception
     *
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
