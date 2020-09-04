<?php

namespace App\Exceptions;

use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Log;
use ReflectionException;
use Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler {
	/**
	 * A list of the exception types that are not reported.
	 *
	 * @var array
	 */
	protected $dontReport = [];

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
	 * @param  \Throwable  $exception
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function report(Throwable $exception) {
		// 记录异常来源
		Log::info('异常来源：'.get_class($exception));

		// 调试模式下记录错误详情
		if(config('app.debug')){
			Log::debug('来自链接：'.url()->full());
			Log::debug($exception);
		}

		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Throwable                $exception
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \Throwable
	 */
	public function render($request, Throwable $exception) {
		// 调试模式下直接返回错误信息
		if(config('app.debug')){
			return parent::render($request, $exception);
		}

		// 捕获访问异常
		if($exception instanceof NotFoundHttpException){
			Log::info("异常请求：".$request->fullUrl()."，IP：".getClientIp());

			if($request->ajax()){
				return Response::json(['status' => 'fail', 'message' => trans('error.MissingPage')]);
			}

			return Response::view('auth.error', ['message' => trans('error.MissingPage')], 404);
		}

		// 捕获身份校验异常
		if($exception instanceof AuthenticationException){
			if($request->ajax()){
				return Response::json(['status' => 'fail', 'message' => trans('error.Unauthorized')]);
			}

			return Response::view('auth.error', ['message' => trans('error.Unauthorized')], 401);
		}

		// 捕获CSRF异常
		if($exception instanceof TokenMismatchException){
			if($request->ajax()){
				return Response::json([
					'status'  => 'fail',
					'message' => trans('error.RefreshPage').'<a href="/login" target="_blank">'.trans('error.Refresh').'</a>'
				]);
			}

			return Response::view('auth.error',
				['message' => trans('error.RefreshPage').'<a href="/login" target="_blank">'.trans('error.Refresh').'</a>'],
				419);
		}

		// 捕获反射异常
		if($exception instanceof ReflectionException){
			if($request->ajax()){
				return Response::json(['status' => 'fail', 'message' => trans('error.SystemError')]);
			}

			return Response::view('auth.error', ['message' => trans('error.SystemError')], 500);
		}

		// 捕获系统错误异常
		if($exception instanceof ErrorException){
			if($request->ajax()){
				return Response::json([
					'status'  => 'fail',
					'message' => trans('error.SystemError').', '.trans('error.Visit').'<a href="/logs" target="_blank">'.trans('error.log').'</a>'
				]);
			}

			return Response::view('auth.error',
				['message' => trans('error.SystemError').', '.trans('error.Visit').'<a href="/logs" target="_blank">'.trans('error.log').'</a>'],
				500);
		}

		return parent::render($request, $exception);
	}
}
