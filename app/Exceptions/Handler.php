<?php

namespace App\Exceptions;

use ErrorException;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use InvalidArgumentException;
use ReflectionException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

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
        // 记录异常来源
        \Log::info('异常来源：' . get_class($exception));

        // 调试模式下记录错误详情
        if (config('app.debug')) {
            \Log::info($exception);
        }

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
        // 调试模式下直接返回错误信息
        if (config('app.debug')) {
            return parent::render($request, $exception);
        }

        // 捕获未生成key异常
        if ($exception instanceof RuntimeException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => $exception->getMessage()]);
            } else {
                return response()->view('auth.error', ['message' => $exception->getMessage()]);
            }
        }

        // 捕获访问异常
        if ($exception instanceof NotFoundHttpException) {
            \Log::info("异常请求：" . $request->fullUrl() . "，IP：" . getClientIp());

            if ($request->expectsJson()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => $exception->getMessage()]);
            } else {
                return response()->view('auth.error', ['message' => $exception->getMessage()]);
            }
        }

        // 路由参数异常
        if ($exception instanceof InvalidArgumentException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => $exception->getMessage()]);
            } else {
                return response()->view('auth.error', ['message' => $exception->getMessage()]);
            }
        }

        // 请求方式不允许异常
        if ($exception instanceof MethodNotAllowedHttpException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => $exception->getMessage()]);
            } else {
                return response()->view('auth.error', ['message' => $exception->getMessage()]);
            }
        }

        // 捕获身份校验异常
        if ($exception instanceof AuthenticationException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => $exception->getMessage()]);
            } else {
                return response()->view('auth.error', ['message' => $exception->getMessage()]);
            }
        }

        // 捕获CSRF异常
        if ($exception instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => 'System Error, Please Refresh Page, Try One More Time']);
            } else {
                return response()->view('auth.error', ['message' => 'System Error, Please Refresh Page, Try One More Time']);
            }
        }

        // 捕获反射异常
        if ($exception instanceof ReflectionException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => 'System Error']);
            } else {
                return response()->view('auth.error', ['message' => 'System Error']);
            }
        }

        // 捕获系统错误异常
        if ($exception instanceof ErrorException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => 'System Error']);
            } else {
                return response()->view('auth.error', ['message' => 'System Error, See <a href="/logs" target="_blank">Logs</a>']);
            }
        }

        // 未授权异常
        if ($exception instanceof UnauthorizedHttpException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'fail', 'data' => '', 'message' => $exception->getMessage()]);
            } else {
                return response()->view('auth.error', ['message' => $exception->getMessage()]);
            }
        }

        // 客户端API：捕获认证过期异常
        if ($exception instanceof TokenExpiredException) {
            return response()->json(['status' => 'fail', 'data' => '', 'message' => $exception->getMessage()]);
        }

        // 客户端API：捕获认证不合法异常
        if ($exception instanceof TokenInvalidException) {
            return response()->json(['status' => 'fail', 'data' => '', 'message' => $exception->getMessage()]);
        }

        return parent::render($request, $exception);
    }
}
