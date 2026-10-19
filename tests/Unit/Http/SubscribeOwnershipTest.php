<?php

namespace Tests\Unit\Http;

use App\Enums\ProxyType;
use App\Models\Country;
use App\Models\Node;
use App\Models\User;
use App\Models\UserSubscribe;
use App\Models\UserSubscribeLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * 节点订阅链接按随机订阅码寻址（UserSubscribe::whereCode）：码即凭据，猜不出、内容互不串、重置或禁用后旧码立即失效.
 */
class SubscribeOwnershipTest extends TestCase
{
    use DatabaseTransactions;

    private User $alice;

    private User $bob;

    protected function setUp(): void
    {
        parent::setUp();

        // 钉住排序与数量上限，避免种子配置（subscribe_max=3 / rand_subscribe）影响断言
        config(['settings.rand_subscribe' => 0, 'settings.subscribe_max' => 0]);

        Country::firstOrCreate(['code' => 'us'], ['name' => '美国']);

        // 节点可见性按等级：alice(0) 看不到 bob(1) 级节点，凭据则始终随订阅用户走
        $this->alice = $this->subscriber('alice', 'ALICECOD', 20001, 'alicePass1', 0);
        $this->bob = $this->subscriber('bob', 'BOBCODEX', 20002, 'bobPass1', 1);
    }

    public function test_随机猜测的订阅码只得到占位配置不泄漏任何内容(): void
    {
        $decoded = $this->decode($this->fetch('ZZ'.Str::upper(Str::random(6))));

        $this->assertStringContainsString(rawurlencode(trans('errors.subscribe.unknown')), $decoded);
        foreach ($this->secrets() as $secret) {
            $this->assertStringNotContainsString($secret, $decoded);
        }
    }

    public function test_非法字符的订阅码被拒绝(): void
    {
        $decoded = $this->decode($this->fetch('bad-code!'));

        $this->assertStringContainsString(rawurlencode(trans('errors.subscribe.unknown')), $decoded);
        foreach ($this->secrets() as $secret) {
            $this->assertStringNotContainsString($secret, $decoded);
        }

        // 订阅页面对非法码直接转回登录页，不查库
        $this->get($this->subUrl('subscribe/bad-code!'))->assertRedirect(route('login'));
    }

    public function test_自己的订阅码取到本人节点并记账(): void
    {
        $response = $this->fetch('ALICECOD');
        $response->assertOk();

        $decoded = $this->decode($response);
        $this->assertStringContainsString('sub-alice.example.com:20001', $decoded);
        $this->assertStringContainsString(base64url_encode('aes-256-gcm:alicePass1'), $decoded);
        $this->assertStringNotContainsString('sub-bob.example.com', $decoded);
        $this->assertStringNotContainsString(base64url_encode('aes-256-gcm:bobPass1'), $decoded);

        $subscribe = UserSubscribe::whereCode('ALICECOD')->firstOrFail();
        $this->assertSame(1, (int) $subscribe->fresh()->times, '成功订阅要计一次访问');
        $this->assertSame(1, UserSubscribeLog::where('user_subscribe_id', $subscribe->id)->count());
    }

    public function test_两个用户的订阅内容互不串(): void
    {
        $aliceOut = $this->decode($this->fetch('ALICECOD'));
        $bobOut = $this->decode($this->fetch('BOBCODEX'));

        $this->assertStringContainsString('sub-alice.example.com:20001', $aliceOut);
        $this->assertStringContainsString(base64url_encode('aes-256-gcm:alicePass1'), $aliceOut);
        $this->assertStringNotContainsString('sub-bob.example.com', $aliceOut);
        $this->assertStringNotContainsString(base64url_encode('aes-256-gcm:bobPass1'), $aliceOut);

        $this->assertStringContainsString('sub-bob.example.com:20002', $bobOut);
        $this->assertStringContainsString(base64url_encode('aes-256-gcm:bobPass1'), $bobOut);
        // 低级节点对高级用户可见（按等级放行），但凭据必须仍是订阅者本人的
        $this->assertStringContainsString('sub-alice.example.com:20002', $bobOut);
        $this->assertStringNotContainsString(base64url_encode('aes-256-gcm:alicePass1'), $bobOut);
    }

    public function test_重置订阅码后旧码立即失效(): void
    {
        UserSubscribe::whereCode('ALICECOD')->update(['code' => 'NEWALICE']);

        $old = $this->decode($this->fetch('ALICECOD'));
        $this->assertStringContainsString(rawurlencode(trans('errors.subscribe.unknown')), $old);
        $this->assertStringNotContainsString('sub-alice.example.com', $old);

        $this->assertStringContainsString('sub-alice.example.com:20001', $this->decode($this->fetch('NEWALICE')));
    }

    public function test_被禁用的订阅码返回封禁占位不泄漏内容(): void
    {
        UserSubscribe::whereCode('BOBCODEX')->update(['status' => 0]);

        $decoded = $this->decode($this->fetch('BOBCODEX'));

        $this->assertStringContainsString(rawurlencode(trans('errors.subscribe.sub_banned')), $decoded);
        $this->assertStringNotContainsString('sub-bob.example.com', $decoded);
        $this->assertStringNotContainsString(base64url_encode('aes-256-gcm:bobPass1'), $decoded);
    }

    public function test_被封禁用户的订阅码返回解封时间占位(): void
    {
        // updateQuietly：跳过 UserObserver，避免测试里触发 VNET/Hysteria2 下发任务
        // 写整数 epoch 才是生产形状：'timestamp' 不在 isDateCastable 里，写入原样透传，
        // 传 Carbon 会经 __toString 变成 'Y-m-d H:i:s' 再被 int 列截断成 2026
        $until = now()->addHour()->timestamp;
        $this->alice->updateQuietly(['enable' => 0, 'ban_time' => $until]);

        $decoded = $this->decode($this->fetch('ALICECOD'));
        $expected = trans('errors.subscribe.banned_until', ['time' => localized_date($until)]);

        $this->assertStringContainsString(rawurlencode($expected), $decoded);
        $this->assertSame($until, (int) $this->alice->fresh()->getRawOriginal('ban_time'), '原始列被截断就说明写入形状又坏了');
        $this->assertStringNotContainsString('sub-alice.example.com', $decoded);
    }

    /** 订阅路由挂 Route::domain，请求主机必须来自 subscribe_domain/website_url 配置 */
    private function subUrl(string $path): string
    {
        $domain = sysConfig('subscribe_domain') ?: sysConfig('website_url');
        $host = rtrim(preg_replace('#^https?://#', '', (string) $domain), '/');

        return ($host !== '' ? 'http://'.$host : '').'/'.$path;
    }

    private function fetch(string $code): TestResponse
    {
        // 带一个不匹配任何客户端 AGENT 的 UA，让订阅走 URLSchemes 的 base64 输出
        return $this->get($this->subUrl('s/'.$code), ['User-Agent' => 'sub-ownership-test']);
    }

    private function decode(TestResponse $response): string
    {
        $content = $response->getContent();

        return base64_decode($content, true) ?: $content;
    }

    /** 两个用户的主机、明文密码与 base64url 后的凭据，任何失败响应里都不许出现 */
    private function secrets(): array
    {
        return [
            'sub-alice.example.com', 'sub-bob.example.com',
            'alicePass1', 'bobPass1',
            base64url_encode('aes-256-gcm:alicePass1'), base64url_encode('aes-256-gcm:bobPass1'),
        ];
    }

    private function subscriber(string $tag, string $code, int $port, string $passwd, int $level): User
    {
        // withoutEvents：跳过 UserObserver（会自动建订阅码并向 VNET 节点同步下发用户）
        $user = User::withoutEvents(fn () => User::create([
            'username' => $tag.'-'.Str::random(8).'@example.com',
            'password' => 'login-pass',
            'nickname' => $tag,
            'port' => $port,
            'passwd' => $passwd,
            'vmess_id' => (string) Str::uuid(),
            'level' => $level,
            'status' => 1,
            'enable' => 1,
        ]));

        UserSubscribe::create(['user_id' => $user->id, 'code' => $code, 'status' => 1]);

        Node::withoutEvents(fn () => Node::create([
            'name' => 'sub-'.$tag,
            'type' => ProxyType::SHADOWSOCKS->value,
            'server' => 'sub-'.$tag.'.example.com',
            'level' => $level,
            'status' => 1,
            'is_display' => 3,
            'is_udp' => 1,
            'country_code' => 'us',
            'profile' => ['method' => 'aes-256-gcm'],
        ]));

        return $user;
    }
}
