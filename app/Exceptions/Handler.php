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
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $exception
     *
     * @return mixed|void
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
        \Log::info("异常请求：" . $request->fullUrl() . "，IP：" . getClientIp());

        // 调试模式下直接返回错误信息
        if (config('app.debug')) {
            return parent::render($request, $exception);
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

        // 捕获反射异常
        if ($exception instanceof ReflectionException) {
            if ($request->ajax()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => 'System Error']);
            } else {
                return response()->view('error.404');
            }
        }

        return response()->view('error.404');
    }
}
