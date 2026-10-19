<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Laravel\Horizon\Exceptions\ForbiddenException;
use ReflectionException;
use RuntimeException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use Throwable;

/**
 * 异常响应只给通用文案：auth.error 直出 $message，异常原文只能进日志。
 * 自带状态码的 HTTP 异常按原码呈现，只有未归类异常才落 400 兜底。
 */
class HandlerResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 非调试分支才是对外服务的分支
        config(['app.debug' => false]);
    }

    public function test_throttled_requests_get_429_and_a_wait_time(): void
    {
        $response = $this->render(new TooManyRequestsHttpException(60, 'Too Many Attempts.'));

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame(trans('auth.throttle', ['seconds' => 60]), $this->message($response));
    }

    public function test_connection_details_are_not_echoed(): void
    {
        $response = $this->render(new ConnectionException('SQLSTATE[HY000] [2002] Unable to connect to 10.0.0.5:3306'));

        $this->assertSame(408, $response->getStatusCode());
        $this->assertStringNotContainsString('10.0.0.5', $this->message($response));
        $this->assertStringNotContainsString('SQLSTATE', $this->message($response));
    }

    public function test_unclassified_exceptions_fall_back_to_a_generic_400(): void
    {
        $response = $this->render(new RuntimeException('fatal in /var/www/html/app/Utils/Payments/EPay.php on line 42'));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringNotContainsString('/var/www/html', $this->message($response));
        $this->assertStringNotContainsString('EPay.php', $this->message($response));
    }

    /**
     * 逐分支钉住对外渲染的状态码与文案：改 Handler 结构时这几条必须先绿。
     *
     * @dataProvider branchProvider
     */
    public function test_each_branch_keeps_its_status_and_copy(Throwable $exception, int $status, string $messageKey): void
    {
        $response = $this->render($exception);

        $this->assertSame($status, $response->getStatusCode());
        $this->assertSame(trans($messageKey), $this->message($response));
    }

    public static function branchProvider(): array
    {
        return [
            [new NotFoundHttpException, 404, 'http-statuses.404'],
            // firstOrFail 的「取不到」必须和真 404 同码，否则同类越权给出两种线索
            [new ModelNotFoundException, 404, 'http-statuses.404'],
            [new AuthenticationException, 401, 'http-statuses.401'],
            [new AccessDeniedHttpException, 401, 'http-statuses.401'],
            [new ReflectionException, 500, 'http-statuses.500'],
            [new MethodNotAllowedHttpException([], 'nope'), 405, 'http-statuses.405'],
            [new UnauthorizedHttpException('Bearer', 'no token'), 401, 'http-statuses.401'],
            // 包自带的 HTTP 异常把状态码带在异常上，照原码呈现
            [ForbiddenException::make(), 403, 'http-statuses.403'],
            [new HttpException(503), 503, 'http-statuses.503'],
        ];
    }

    public function test_route_name_rbac_denial_is_not_reported_as_a_bad_request(): void
    {
        $response = $this->render(UnauthorizedException::forPermissions(['horizon.index']));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(trans('http-statuses.403'), $this->message($response));
        $this->assertStringNotContainsString('horizon.index', $this->message($response));

        // 浏览器打开面板的那一路：页面文案跟着原码走，不再报成「请求错误」
        $page = app(Handler::class)->render(Request::create('/admin/horizon', 'GET'), ForbiddenException::make());

        $this->assertSame(403, $page->getStatusCode());
        $this->assertSame('auth.error', $page->getOriginalContent()->getName());
        $this->assertSame(trans('http-statuses.403'), $page->getOriginalContent()->getData()['message']);
    }

    public function test_the_log_link_branches_keep_their_own_copy(): void
    {
        $error = $this->render(new ErrorException('boom', 0, E_ERROR, '/var/www/x.php', 1));

        $this->assertSame(500, $error->getStatusCode());
        $this->assertStringContainsString(trans('errors.visit'), $this->message($error));
        $this->assertStringContainsString(route('log-viewer::dashboard'), $this->message($error));
    }

    public function test_html_requests_get_the_error_view_instead_of_json(): void
    {
        $response = app(Handler::class)->render(Request::create('/user/invoice', 'GET'), new NotFoundHttpException);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('auth.error', $response->getOriginalContent()->getName());
        $this->assertSame(trans('http-statuses.404'), $response->getOriginalContent()->getData()['message']);
    }

    public function test_the_csrf_view_is_the_only_branch_that_appends_a_refresh_link(): void
    {
        $response = app(Handler::class)->render(Request::create('/user/order', 'POST'), new TokenMismatchException);

        $this->assertSame(419, $response->getStatusCode());
        $this->assertStringContainsString(trans('errors.refresh_page'), $response->getOriginalContent()->getData()['message']);
        $this->assertStringContainsString(route('login'), $response->getOriginalContent()->getData()['message']);
    }

    private function message($response): string
    {
        return json_decode($response->getContent(), true)['message'] ?? '';
    }

    private function render(Throwable $exception)
    {
        $request = Request::create('/login', 'POST');
        $request->headers->set('Accept', 'application/json');

        return app(Handler::class)->render($request, $exception);
    }
}
