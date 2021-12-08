<?php

namespace App\Exceptions;

use App\Components\IP;
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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ValidationException::class,
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
     * @param  Throwable  $exception
     * @return void
     *
     * @throws Throwable
     */
    public function report(Throwable $exception)
    {
        if (config('app.debug') || config('app.demo')) { // 调试模式下记录错误详情
            Log::debug('来自链接：'.url()->full());
            Log::debug($exception);
        } else {
            Log::error('异常来源：'.get_class($exception)); // 记录异常来源
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Throwable  $exception
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
                        return Response::json(['status' => 'fail', 'message' => trans('errors.missing_page')], 404);
                    }

                    return Response::view('auth.error', ['message' => trans('errors.missing_page')], 404);
                case $exception instanceof AuthenticationException:  // 捕获身份校验异常
                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json(['status' => 'fail', 'message' => trans('errors.unauthorized')], 401);
                    }

                    return Response::view('auth.error', ['message' => trans('errors.unauthorized')], 401);
                case $exception instanceof TokenMismatchException: // 捕获CSRF异常
                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json([
                            'status'  => 'fail',
                            'message' => trans('errors.refresh_page').'<a href="'.route('login').'" target="_blank">'.trans('errors.refresh').'</a>',
                        ], 419);
                    }

                    return Response::view(
                        'auth.error',
                        ['message' => trans('errors.refresh_page').'<a href="'.route('login').'" target="_blank">'.trans('errors.refresh').'</a>'],
                        419
                    );
                case $exception instanceof ReflectionException:
                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json(['status' => 'fail', 'message' => trans('errors.system')], 500);
                    }

                    return Response::view('auth.error', ['message' => trans('errors.system')], 500);
                case $exception instanceof ErrorException: // 捕获系统错误异常
                    if ($request->ajax() || $request->wantsJson()) {
                        return Response::json([
                            'status'  => 'fail',
                            'message' => trans('errors.system').', '.trans('errors.visit').'<a href="'.route('log-viewer::dashboard').'" target="_blank">'.trans('errors.log').'</a>',
                        ], 500);
                    }

                    return Response::view(
                        'auth.error',
                        ['message' => trans('errors.system').', '.trans('errors.visit').'<a href="'.route('log-viewer::dashboard').'" target="_blank">'.trans('errors.log').'</a>'],
                        500
                    );
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
