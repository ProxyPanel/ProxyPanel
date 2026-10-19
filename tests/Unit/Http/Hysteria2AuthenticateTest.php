<?php

namespace Tests\Unit\Http;

use App\Enums\ProxyType;
use App\Models\Node;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Hysteria2 的 auth/{node} 端点不走 webApi 中间件（withoutMiddleware）：
 * 只按 auth=port:passwd 对面板用户鉴权，通过后记录在线 IP 并返回字符串用户 id。
 */
class Hysteria2AuthenticateTest extends TestCase
{
    use DatabaseTransactions;

    private Node $node;

    protected function setUp(): void
    {
        parent::setUp();

        $this->node = $this->node();
    }

    public function test_valid_credentials_pass_and_record_the_online_ip(): void
    {
        $user = $this->user();

        $response = $this->authenticate($this->node, ['auth' => $user->port.':'.$user->passwd, 'addr' => '203.0.113.7:52000']);

        $response->assertOk()->assertJson(['ok' => true, 'id' => (string) $user->id]);

        $online = $this->node->onlineIps()->where('user_id', $user->id)->first();
        $this->assertNotNull($online);
        $this->assertSame('203.0.113.7', $online->ip);
        $this->assertSame(52000, (int) $online->port);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = $this->user();

        $this->authenticate($this->node, ['auth' => $user->port.':wrong-password'])
            ->assertOk()
            ->assertJson(['ok' => false, 'error' => 'Password incorrect']);
    }

    public function test_unknown_port_is_rejected(): void
    {
        $ghostPort = (int) User::max('port') + 1; // 按定义不会命中任何账号

        $this->authenticate($this->node, ['auth' => $ghostPort.':whatever'])
            ->assertOk()
            ->assertJson(['ok' => false, 'error' => 'User not found']);
    }

    public function test_disabled_user_is_rejected(): void
    {
        $user = $this->user(['enable' => 0]);

        $this->authenticate($this->node, ['auth' => $user->port.':'.$user->passwd])
            ->assertOk()
            ->assertJson(['ok' => false, 'error' => 'User was disabled']);
    }

    public function test_user_without_access_to_the_node_is_rejected(): void
    {
        $node = $this->node(['level' => 3]); // 0 级用户的可见节点里没有它
        $user = $this->user();

        $this->authenticate($node, ['auth' => $user->port.':'.$user->passwd])
            ->assertOk()
            ->assertJson(['ok' => false, 'error' => 'Does not have permission to access this node']);
    }

    public function test_malformed_auth_is_rejected(): void
    {
        $this->authenticate($this->node, ['auth' => 'not-a-port-pair'])
            ->assertOk()
            ->assertJson(['ok' => false, 'error' => 'Invalid request format']);
    }

    /** 端点整组仍受域名约束，Host 头必须匹配 web_api_url/website_url */
    private function authenticate(Node $node, array $payload): TestResponse
    {
        $host = parse_url(sysConfig('web_api_url') ?: sysConfig('website_url'), PHP_URL_HOST) ?: 'localhost';

        return $this->postJson('/api/hysteria2/v1/auth/'.$node->id, $payload, ['Host' => $host]);
    }

    private function node(array $attributes = []): Node
    {
        return Node::withoutEvents(fn () => Node::create(array_merge([
            'name' => 'hy2-auth-'.uniqid(),
            'type' => ProxyType::HYSTERIA2->value,
            'profile' => [],
            'server' => 'hy2-auth.test',
            'is_ddns' => 1,
        ], $attributes)));
    }

    private function user(array $attributes = []): User
    {
        return User::create(array_merge([
            'username' => 'hy2-auth-'.uniqid().'@example.com',
            'passwd' => 'ProxyPass!1',
            'port' => 30000 + random_int(1, 9999),
        ], $attributes));
    }
}
