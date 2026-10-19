<?php

namespace App\Exceptions;

use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as PlainResponse;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Log;
use ReflectionException;
use Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
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
            Log::debug('来源：'.url()->full().PHP_EOL.'访问者IP：'.request()->ip().PHP_EOL.$exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * 非调试模式下只可能返回带文案的或不带文案的两种响应，这里不再抛第二次异常。
     *
     * @param  Request  $request
     * @return BaseResponse
     */
    public function render($request, Throwable $exception)
    {
        // 调试模式下直接返回错误信息，非调试模式下渲染在返回
        if (config('app.debug')) {
            return parent::render($request, $exception);
        }

        // translator 是延迟服务、路由表由 RouteServiceProvider 在 boot 阶段加载：服务提供者没全部 boot 完时文案取不到
        if (! $this->presentationReady()) {
            return $this->renderWithoutCopy($request, $exception);
        }

        try {
            return $this->renderLocalized($request, $exception);
        } catch (Throwable $e) {
            // 文案链路上任何服务半路坏掉都要降级并留下死因：这条异常不会再经 report() 落日志
            try {
                Log::warning('异常渲染已降级：'.$e->getMessage());
            } catch (Throwable) {
            }

            return $this->renderWithoutCopy($request, $exception);
        }
    }

    /**
     * 带文案的分支：状态码与文案取自已成形的分类，页面分支走 auth.error。
     */
    private function renderLocalized($request, Throwable $exception): BaseResponse
    {
        // 404 类先留痕：真 404 与取不到归属记录都只写日志，不外泄存在性
        if ($exception instanceof NotFoundHttpException || $exception instanceof ModelNotFoundException) {
            Log::warning('异常请求：'.$request->fullUrl().'，IP：'.$request->ip());
        }

        if ($exception instanceof ConnectionException) {
            // auth.error 直出 $message，原文只进日志
            Log::warning(trans('http-statuses.408').': '.$exception->getMessage());
        }

        $seconds = (int) ($exception instanceof TooManyRequestsHttpException
            ? ($exception->getHeaders()['Retry-After'] ?? 60)
            : 60);

        [$status, $jsonKey, $viewKey] = $this->branch($exception);

        // 页面分支才带可点链接，接口分支只回状态码对应文案
        return $request->ajax() || $request->wantsJson()
            ? Response::json(['status' => 'fail', 'message' => $this->message($jsonKey, $seconds)], $status)
            : Response::view('auth.error', ['message' => $this->message($viewKey, $seconds)], $status);
    }

    /**
     * 不带文案的分支：不碰 translator、路由表、视图与日志器，内容只有固定文本，异常原文不外泄。
     *
     * 这条分支不补日志：触发它的异常已经由 report() 落过一次，拒连窗口里每请求多一行就是日志洪水。
     */
    private function renderWithoutCopy($request, Throwable $exception): BaseResponse
    {
        [$status] = $this->branch($exception);
        // 渲染降级本身是服务器故障：未归类异常不能照抄兜底的 400，否则整站起不来会被探活当成客户端错误
        $status = $status === 400 ? 500 : $status;

        return $request->ajax() || $request->wantsJson()
            ? new JsonResponse(['status' => 'fail', 'message' => 'Server Error'], $status)
            : new PlainResponse('Server Error', $status, ['Content-Type' => 'text/plain']);
    }

    /**
     * 状态码与文案键的唯一分类处：判据只碰 instanceof 与异常自身，容器未就绪时同样可跑。
     *
     * 每臂的状态码与文案都已成形（HandlerResponseTest 逐条钉住）；新增异常只加臂，别调顺序。
     *
     * @return array{0: int, 1: string, 2: string}
     */
    private function branch(Throwable $exception): array
    {
        return match (true) {
            $exception instanceof NotFoundHttpException,
            $exception instanceof ModelNotFoundException => [404, 'http-statuses.404', 'http-statuses.404'],
            $exception instanceof AuthenticationException,
            $exception instanceof AccessDeniedHttpException => [401, 'http-statuses.401', 'http-statuses.401'],
            $exception instanceof TokenMismatchException => [419, 'http-statuses.419', 'csrf_link'],
            $exception instanceof ReflectionException => [500, 'http-statuses.500', 'http-statuses.500'],
            $exception instanceof MethodNotAllowedHttpException => [405, 'http-statuses.405', 'http-statuses.405'],
            $exception instanceof ErrorException => [500, 'log_link', 'log_link'],
            $exception instanceof TooManyRequestsHttpException => [429, 'auth.throttle', 'auth.throttle'],
            $exception instanceof ConnectionException => [408, 'http-statuses.408', 'http-statuses.408'],
            // 包自带的 HTTP 异常把状态码带在异常上（Horizon 的 403、spatie 的权限拒绝），照原码呈现，别压成 400
            $exception instanceof HttpExceptionInterface => [$status = $exception->getStatusCode(), 'http-statuses.'.$status, 'http-statuses.'.$status],
            default => [400, 'http-statuses.400', 'http-statuses.400'],
        };
    }

    /**
     * 文案只在使用它的那一臂与那一路各算一次；两个不带点的伪键要查路由表，所以不能提前拼。
     */
    private function message(string $key, int $seconds): string
    {
        return match ($key) {
            'auth.throttle' => trans('auth.throttle', ['seconds' => $seconds]),
            'log_link' => trans('http-statuses.500').', '.trans('errors.visit').'<a href="'.route('log-viewer::dashboard').'" target="_blank">'.trans('errors.log').'</a>',
            'csrf_link' => trans('errors.refresh_page').'<a href="'.route('login').'" target="_blank">'.trans('errors.refresh').'</a>',
            default => trans($key),
        };
    }

    /**
     * 容器能否承担带文案的渲染。
     */
    private function presentationReady(): bool
    {
        // deferred 服务在 boot 前后都可能「已登记未绑定」，bound('translator') 在正常请求里也不成立，判据只能取 boot 完成
        return $this->container instanceof Application && $this->container->isBooted();
    }
}
