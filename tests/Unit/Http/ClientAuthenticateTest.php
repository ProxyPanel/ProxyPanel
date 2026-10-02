<?php

namespace Tests\Unit\Http;

use App\Http\Middleware\ClientAuthenticate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * 客户端接口的身份来源：Bearer token 与客户端自己写的 session uid。
 */
class ClientAuthenticateTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create(['username' => 'client-'.uniqid().'@example.com', 'passwd' => bcrypt('secret-123')]);
    }

    public function test_panel_web_session_alone_is_not_a_client_credential(): void
    {
        // sanctum.guard 默认含 web：没有 Bearer token 时不能让 web 会话接管 /api/v1/*
        $this->actingAs($this->user, 'web');

        $data = $this->through($this->clientRequest())->getData(true);

        $this->assertArrayNotHasKey('passed', $data);
        $this->assertSame('fail', $data['status'] ?? null);
    }

    public function test_session_uid_written_by_the_client_login_still_works(): void
    {
        $request = $this->clientRequest();
        $request->session()->put('uid', $this->user->id);

        $this->assertTrue($this->through($request)->getData(true)['passed']);
    }

    public function test_bearer_token_authenticates(): void
    {
        $request = $this->clientRequest($this->user->createToken('client')->plainTextToken);

        $this->assertTrue($this->through($request)->getData(true)['passed']);
    }

    public function test_banned_user_is_rejected_even_with_a_valid_token(): void
    {
        $this->user->update(['status' => -1]);

        $request = $this->clientRequest($this->user->createToken('client')->plainTextToken);

        $this->assertSame('fail', $this->through($request)->getData(true)['status']);
    }

    /** 真实走一遍路由：中间件没挂上或判据被改掉时，这条要比单元测试更早发现 */
    public function test_panel_web_session_cannot_read_the_client_api_over_http(): void
    {
        $this->actingAs($this->user, 'web');

        $response = $this->getJson('/api/v1/getuserinfo');

        $this->assertSame('fail', $response->json('status'));
        $this->assertNull($response->json('data.info'));
    }

    private function clientRequest(?string $token = null): Request
    {
        $request = Request::create('/api/v1/getuserinfo', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        // 手搓的 Request 不经容器解析，不绑 resolver 时 $request->user() 恒为 null
        $request->setUserResolver(fn (?string $guard = null) => auth($guard)->user());

        if ($token !== null) {
            $request->headers->set('Authorization', 'Bearer '.$token);
        }

        $this->app->instance('request', $request);

        return $request;
    }

    private function through(Request $request): JsonResponse
    {
        return (new ClientAuthenticate($request))->handle($request, fn () => new JsonResponse(['passed' => true]));
    }
}
