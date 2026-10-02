<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tests\TestCase;
use Throwable;

/**
 * 异常响应只给通用文案：auth.error 直出 $message，异常原文只能进日志。
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
