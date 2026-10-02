<?php

namespace Tests\Unit\Http;

use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * 命名限流器的档位与计数键；路由上只写 throttle:<名字>，阈值由这里兜住。
 */
class RateLimitTierTest extends TestCase
{
    private function limits(string $name, Request $request): array
    {
        $limit = app(RateLimiter::class)->limiter($name)($request);

        return is_array($limit) ? $limit : [$limit];
    }

    private function loginRequest(): Request
    {
        $request = Request::create('/login', 'POST', ['username' => 'victim@example.com', 'password' => 'x']);
        // 让 ip() 落到可预期的值，别受本机网络栈影响
        $request->server->set('REMOTE_ADDR', '203.0.113.7');

        return $request;
    }

    public function test_login_is_limited_per_account_and_per_ip(): void
    {
        $limits = $this->limits('login', $this->loginRequest());

        $this->assertCount(2, $limits, '登录必须有双档：按 IP+账号 与 按 IP');

        [$perAccount, $perIp] = $limits;
        $this->assertInstanceOf(Limit::class, $perAccount);
        // 真正拦住撞库的是这一档：同一个账号一分钟只容 5 次
        $this->assertSame(5, $perAccount->maxAttempts);
        $this->assertSame('203.0.113.7|victim@example.com', $perAccount->key);
        // IP 档要宽到容得下共享出口 IP（NAT 后多人各自登录是正常流量）
        $this->assertSame(60, $perIp->maxAttempts);
        $this->assertSame('203.0.113.7', $perIp->key);
    }

    public function test_register_and_mail_and_token_actions_have_their_own_tiers(): void
    {
        $request = Request::create('/register', 'POST');
        $request->server->set('REMOTE_ADDR', '203.0.113.9');

        // 注册名额由后台 register_ip_limit 决定，分钟档只拦脚本洪水，不比运营阈值更紧
        $this->assertSame(20, $this->limits('register', $request)[0]->maxAttempts);
        // 会发邮件的三个入口合用一档：拦轰炸，但留出同一 NAT 后多人点「忘记密码」的余量
        $this->assertSame(20, $this->limits('mail-actions', $request)[0]->maxAttempts);
        // 消费校验令牌的动作：给穷举加成本，同时容得下「点开链接、填错一次再提交」
        $this->assertSame(20, $this->limits('verify-token', $request)[0]->maxAttempts);

        $this->assertSame('203.0.113.9', $this->limits('mail-actions', $request)[0]->key);
    }

    public function test_login_account_tier_is_case_insensitive_like_the_database_lookup(): void
    {
        // 账号列是 utf8mb4_general_ci，限流键也得归一大小写才对得上同一判据
        $upper = Request::create('/login', 'POST', ['username' => 'Victim@Example.com']);
        $lower = Request::create('/login', 'POST', ['username' => 'victim@example.com']);
        foreach ([$upper, $lower] as $r) {
            $r->server->set('REMOTE_ADDR', '203.0.113.21');
        }

        $this->assertSame(
            $this->limits('login', $lower)[0]->key,
            $this->limits('login', $upper)[0]->key
        );
        $this->assertSame('203.0.113.21|victim@example.com', $this->limits('login', $upper)[0]->key);
    }

    public function test_client_api_auth_has_its_own_tiers(): void
    {
        // /api/v1/login 与 /api/v1/register 未鉴权即可试账号，bob 那套用 email 字段，也要进同一个键
        $request = Request::create('/api/v1/login', 'POST', ['email' => 'someone@example.com']);
        $request->server->set('REMOTE_ADDR', '203.0.113.31');

        [$perAccount, $perIp] = $this->limits('client-auth', $request);

        $this->assertSame(5, $perAccount->maxAttempts);
        $this->assertSame('203.0.113.31|someone@example.com', $perAccount->key);
        $this->assertSame(30, $perIp->maxAttempts);
    }

    public function test_payment_callback_tier_is_loose_enough_for_gateway_retries(): void
    {
        $request = Request::create('/callback/notify?method=epay', 'POST');
        $request->server->set('REMOTE_ADDR', '203.0.113.11');

        $limit = $this->limits('payment-callback', $request)[0];

        // 这条既不能紧到把聚合支付的批量回传挡掉（丢单），也不能没有（未鉴权还每条写库 = 免费刷表）
        $this->assertSame(200, $limit->maxAttempts);
        $this->assertSame('203.0.113.11', $limit->key);
    }

    public function test_api_tier_keys_on_the_user_when_authenticated(): void
    {
        $guest = Request::create('/api/v1/getuserinfo', 'GET');
        $guest->server->set('REMOTE_ADDR', '203.0.113.13');

        $this->assertSame(60, $this->limits('api', $guest)[0]->maxAttempts);
        $this->assertSame('203.0.113.13', $this->limits('api', $guest)[0]->key);
    }
}
