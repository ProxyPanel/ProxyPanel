<?php

namespace Tests\Unit\Jobs;

use App\Enums\ProxyType;
use App\Jobs\Hysteria2\DelUser;
use App\Jobs\Hysteria2\GetOnlineUser;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Models\User;
use Http;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Tests\TestCase;

/**
 * Hysteria2 推送侧的请求形状：GetOnlineUser 按 hosts() 轮询 /online（DDNS 走 server:push_port，否则逐 IP），
 * DelUser 向同一组地址 POST /kick；两者都拿 node_auth.secret 当 Authorization 头，uid 必须是字符串。
 */
class Hysteria2PushTest extends TestCase
{
    use DatabaseTransactions;

    private Node $ddnsNode;

    private Node $ipNode;

    private string $secret = 'abcd1234';

    protected function setUp(): void
    {
        parent::setUp();

        $this->ddnsNode = $this->node(['server' => 'hy2-push.test', 'is_ddns' => 1, 'push_port' => 9090]);
        $this->ipNode = $this->node(['ip' => '10.9.0.1,10.9.0.2', 'is_ddns' => 0, 'push_port' => 8080]);
    }

    public function test_get_online_user_polls_the_ddns_server(): void
    {
        $user = $this->user();
        Http::fake(fn () => Http::response([$user->id => 2]));

        (new GetOnlineUser($this->ddnsNode))->handle();

        // base_uri 不带 scheme，URL 整体匹配不稳，按 host/端口/路径分段断言
        Http::assertSent(fn (Request $request) => $request->method() === 'GET'
            && str_contains($request->url(), 'hy2-push.test')
            && str_contains($request->url(), '9090')
            && str_contains($request->url(), '/online')
            && $request->hasHeader('Authorization', $this->secret));
        Http::assertSentCount(1);

        $this->assertSame(1, $this->ddnsNode->onlineLogs()->count());
        $this->assertSame(1, (int) $this->ddnsNode->onlineLogs()->latest('id')->first()->online_user);
        $this->assertSame(60, (int) $this->ddnsNode->heartbeat->uptime); // 首次观测的运行时长基数
        $this->assertNotNull(User::find($user->id)->t); // Hysteria2 没有流量上报通道，t 由轮询观测补写
    }

    public function test_get_online_user_polls_every_ip_when_not_ddns(): void
    {
        Http::fake(fn () => Http::response([]));

        (new GetOnlineUser($this->ipNode))->handle();

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '10.9.0.1')
            && str_contains($request->url(), '8080')
            && str_contains($request->url(), '/online')
            && $request->hasHeader('Authorization', $this->secret));
        Http::assertSent(fn (Request $request) => str_contains($request->url(), '10.9.0.2')
            && str_contains($request->url(), '8080')
            && str_contains($request->url(), '/online'));
        Http::assertSentCount(2);
    }

    public function test_get_online_user_skips_node_without_secret(): void
    {
        NodeAuth::whereNodeId($this->ddnsNode->id)->delete();
        Http::fake();

        (new GetOnlineUser($this->ddnsNode->fresh()))->handle();

        Http::assertNothingSent();
    }

    public function test_revoked_online_user_gets_kicked(): void
    {
        $user = $this->user();
        $ghost = (int) User::max('id') + 9999; // 节点会话表里残留的已删账号
        Http::fake(fn () => Http::response([$user->id => 1, $ghost => 1]));

        (new GetOnlineUser($this->ddnsNode))->handle();

        // QUEUE_CONNECTION=sync：踢下线的 DelUser 当场执行，/kick 收字符串 uid 数组
        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && str_contains($request->url(), 'hy2-push.test')
            && str_contains($request->url(), '/kick')
            && $request->body() === json_encode([(string) $ghost]));
        $this->assertSame(1, (int) $this->ddnsNode->onlineLogs()->latest('id')->first()->online_user, '被踢的账号不计入在线');
    }

    public function test_del_user_posts_string_uids_to_the_push_port(): void
    {
        Http::fake(fn () => Http::response(['ok' => true]));

        (new DelUser([7], $this->ddnsNode))->handle();

        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && str_contains($request->url(), 'hy2-push.test')
            && str_contains($request->url(), '9090')
            && str_contains($request->url(), '/kick')
            && $request->hasHeader('Authorization', $this->secret)
            && $request->hasHeader('Content-Type', 'application/json')
            && $request->body() === '["7"]');
    }

    public function test_del_user_posts_to_every_ip_when_not_ddns(): void
    {
        Http::fake(fn () => Http::response(['ok' => true]));

        (new DelUser([7, 8], $this->ipNode))->handle();

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '10.9.0.1')
            && str_contains($request->url(), '8080')
            && str_contains($request->url(), '/kick')
            && $request->body() === '["7","8"]');
        Http::assertSent(fn (Request $request) => str_contains($request->url(), '10.9.0.2')
            && str_contains($request->url(), '8080')
            && str_contains($request->url(), '/kick'));
        Http::assertSentCount(2);
    }

    private function node(array $attributes): Node
    {
        $node = Node::withoutEvents(fn () => Node::create(array_merge([
            'name' => 'hy2-push-'.uniqid(),
            'type' => ProxyType::HYSTERIA2->value,
            'profile' => [],
            'level' => 0, // 显式给：库里默认值不会水合回模型实例，eligibleUserIds 的 where 会拿到 null
        ], $attributes)));
        NodeAuth::create(['node_id' => $node->id, 'key' => 'abcdef0123456789', 'secret' => $this->secret]);

        return $node;
    }

    private function user(): User
    {
        return User::create([
            'username' => 'hy2-push-'.uniqid().'@example.com',
            'passwd' => 'ProxyPass!1',
            'port' => 30000 + random_int(1, 9999),
        ]);
    }
}
