<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use ErrorException;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Support\Facades\Response as ResponseFacade;
use PDOException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/**
 * bootstrap 期（服务提供者没跑完）的渲染判据：文案、路由表与视图这时都取不到，
 * render() 只能不碰容器地把状态码回出去；取文案会把渲染打成第二次异常，客户端只剩白屏。
 *
 * 现场是一个只绑了 config 与基础提供者的裸容器，所以这里不启动应用、不连数据库。
 */
class HandlerBootstrapRenderTest extends TestCase
{
    /**
     * @var Application|null
     */
    private $app;

    /**
     * @var Container|null
     */
    private $previousFacadeApplication;

    protected function setUp(): void
    {
        $this->previousFacadeApplication = Facade::getFacadeApplication();

        $app = new Application;
        $app->instance('config', new Repository(['app' => ['debug' => false]]));
        Container::setInstance($app);
        // 真实请求里别名由 RegisterFacades 装好；这里不装别名，文案分支第一行留痕就会以 Class "Log" not found 失败，兜底是被错误原因触发的
        AliasLoader::getInstance(['Log' => LogFacade::class, 'Response' => ResponseFacade::class])->register();
        Facade::setFacadeApplication($app);

        $this->app = $app;
    }

    protected function tearDown(): void
    {
        // 容器与 Facade 应用是进程级静态，不还回去会污染同套件后面的用例
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousFacadeApplication);
        Container::setInstance(null);

        $this->app = null;
        $this->previousFacadeApplication = null;
    }

    public function test_api_requests_degrade_to_json_without_touching_the_container(): void
    {
        $response = $this->render($this->jsonRequest(), $this->refused());

        // 降级是服务器故障：未归类的库异常在兜底里落 500，不能回成客户端错误
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(['status' => 'fail', 'message' => 'Server Error'], json_decode($response->getContent(), true));
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        $this->assertStringNotContainsString('Connection refused', $response->getContent());
        $this->assertStringNotContainsString('information_schema', $response->getContent());
    }

    public function test_page_requests_degrade_to_plain_text_instead_of_the_error_view(): void
    {
        // auth.error 要 sysConfig() 与 trans()，bootstrap 期渲染它就是第二次异常
        $response = $this->render(Request::create('/user/index', 'GET'), $this->refused());

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Server Error', $response->getContent());
        $this->assertStringNotContainsString('/www/wwwroot/panel', $response->getContent());
    }

    public function test_the_status_code_still_follows_the_exception_type(): void
    {
        $this->assertSame(404, $this->render(Request::create('/nope', 'GET'), new NotFoundHttpException)->getStatusCode());
        $this->assertSame(429, $this->render($this->jsonRequest(), new TooManyRequestsHttpException(60, 'Too Many Attempts.'))->getStatusCode());
        $this->assertSame(500, $this->render($this->jsonRequest(), new ReflectionException('Class "translator" does not exist'))->getStatusCode());
        $this->assertSame(500, $this->render($this->jsonRequest(), new ErrorException('boom', 0, E_ERROR, '/var/www/x.php', 1))->getStatusCode());
    }

    public function test_the_second_order_exceptions_from_the_bootstrap_failure_degrade(): void
    {
        // 现场第 2、3 条日志就是这两个：兜底再依赖 translator 或路由表就还是白屏
        $resolution = $this->render($this->jsonRequest(), new BindingResolutionException('Target class [translator] does not exist.'));
        $fatal = $this->render(Request::create('/user/order', 'POST'), new ErrorException('Uncaught ReflectionException: Class "translator" does not exist in /www/wwwroot/panel/app/Exceptions/Handler.php'));

        $this->assertSame(500, $resolution->getStatusCode());
        $this->assertStringNotContainsString('translator', $resolution->getContent());
        $this->assertSame(500, $fatal->getStatusCode());
        $this->assertStringNotContainsString('/www/', $fatal->getContent());
        $this->assertStringNotContainsString('Handler.php', $fatal->getContent());
    }

    public function test_the_fallback_writes_no_log_line_of_its_own(): void
    {
        // 一次拒连就是一次全站失败：兜底再补一行会把日志刷成洪水，触发它的异常已由 report() 落过
        Facade::setFacadeApplication($this->app);
        $logger = new class
        {
            /** @var array<int, string> */
            public $written = [];

            public function warning($message, array $context = []): void
            {
                $this->written[] = $message;
            }

            public function error($message, array $context = []): void
            {
                $this->written[] = $message;
            }
        };
        $this->app->instance('log', $logger);

        $response = $this->render($this->jsonRequest(), new ReflectionException('Class "translator" does not exist.'));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Server Error', $this->payload($response)['message']);
        $this->assertSame([], $logger->written);
    }

    public function test_the_copy_branch_degrades_when_translation_breaks_after_boot(): void
    {
        // boot 完成后判据放行文案分支，此时 translator 坏掉同样只能降级
        Facade::setFacadeApplication($this->app);
        $logger = new class
        {
            /** @var array<int, string> */
            public $warnings = [];

            public function warning($message, array $context = []): void
            {
                $this->warnings[] = $message;
            }
        };
        $this->app->instance('log', $logger);
        $this->app->instance('translator', new class
        {
            public function get($key, array $replace = [], $locale = null)
            {
                throw new RuntimeException('文案取不到');
            }
        });
        $this->app->boot();

        $response = $this->render($this->jsonRequest(), new NotFoundHttpException);

        $this->assertNotEmpty($logger->warnings, '留痕日志没跑，说明进的不是文案分支');
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Server Error', $this->payload($response)['message']);
    }

    /**
     * 生产现场的第一条异常形态：AppServiceProvider 注册期查库拒连。
     */
    private function refused(): QueryException
    {
        $sql = 'select table_name as `name`, (data_length + index_length) as `size` from information_schema.tables where table_schema = ?';

        return new QueryException('mysql', $sql, ['panel'], new PDOException('SQLSTATE[HY000] [2002] Connection refused'));
    }

    private function render(Request $request, Throwable $exception)
    {
        return (new Handler($this->app))->render($request, $exception);
    }

    private function jsonRequest(): Request
    {
        $request = Request::create('/api/v1/user/login', 'POST');
        $request->headers->set('Accept', 'application/json');

        return $request;
    }

    private function payload($response): array
    {
        return json_decode($response->getContent(), true) ?? [];
    }
}
