<?php

namespace App\Exceptions;

use App\Utils\IP;
use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Log;
use ReflectionException;
use Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        ConnectionException::class,
        ValidationException::class,
    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function report(Throwable $exception)
    {
        if (config('app.debug')) { // 调试模式下记录错误详情
            Log::debug('来源：'.url()->full().PHP_EOL.'访问者IP：'.IP::getClientIP().PHP_EOL.$exception);
        } else {
            Log::error('来源：'.url()->full().PHP_EOL.'访问者IP：'.IP::getClientIP().get_class($exception)); // 记录异常来源
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        // 调试模式下直接返回错误信息，非调试模式下渲染在返回
        if (! config('app.debug')) {
            switch ($exception) {
                case $exception instanceof NotFoundHttpException: // 捕获访问异常
                    Log::warning('异常请求：'.$request->fullUrl().'，IP：'.IP::getClientIp());

                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json(['status' => 'fail', 'message' => trans('http-statuses.404')], 404);
                    }

                    return Response::view('auth.error', ['message' => trans('http-statuses.404')], 404);
                case $exception instanceof AuthenticationException:  // 捕获身份校验异常
                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json(['status' => 'fail', 'message' => trans('http-statuses.401')], 401);
                    }

                    return Response::view('auth.error', ['message' => trans('http-statuses.401')], 401);
                case $exception instanceof TokenMismatchException: // 捕获CSRF异常
                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json(['status' => 'fail', 'message' => trans('http-statuses.419')], 419);
                    }

                    return Response::view('auth.error', ['message' => trans('errors.refresh_page').'<a href="'.route('login').'" target="_blank">'.trans('errors.refresh').'</a>'],
                        419);
                case $exception instanceof ReflectionException:
                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json(['status' => 'fail', 'message' => trans('http-statuses.500')], 500);
                    }

                    return Response::view('auth.error', ['message' => trans('http-statuses.500')], 500);
                case $exception instanceof MethodNotAllowedHttpException:
                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json(['status' => 'fail', 'message' => trans('http-statuses.405')], 405);
                    }

                    return Response::view('auth.error', ['message' => trans('http-statuses.405')], 405);
                case $exception instanceof ErrorException: // 捕获系统错误异常
                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json([
                            'status' => 'fail',
                            'message' => trans('http-statuses.500').', '.trans('errors.visit').'<a href="'.route('log-viewer::dashboard').'" target="_blank">'.trans('errors.log').'</a>',
                        ], 500);
                    }

                    return Response::view('auth.error',
                        ['message' => trans('http-statuses.500').', '.trans('errors.visit').'<a href="'.route('log-viewer::dashboard').'" target="_blank">'.trans('errors.log').'</a>'],
                        500);
                case $exception instanceof ConnectionException:
                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json(['status' => 'fail', 'message' => $exception->getMessage()], 408);
                    }

                    return Response::view('auth.error', ['message' => $exception->getMessage()], 408);
            }
        }

        return parent::render($request, $exception);
    }
}
