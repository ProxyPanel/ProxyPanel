<?php

namespace Tests\Unit\Jobs;

use App\Jobs\VNet\GetUser;
use App\Jobs\VNet\NodeReachability;
use App\Models\Node;
use App\Models\NodeAuth;
use Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * 对账要把「节点上没人」和「拿不到节点的集合」分开：两者都返回 false 时，
 * 差集会把「拉取失败」读成「节点集合为空」，进而把该节点的用户当成要新增的一批。
 */
class VNetGetUserListTest extends TestCase
{
    /** 夹具节点的寻址地址：可达性窗口存在 cache 里，不跟着事务回滚 */
    private const HOST = '127.0.0.1:443';

    private Node $node;

    protected function setUp(): void
    {
        parent::setUp();

        NodeReachability::clear(self::HOST);

        $node = (new Node)->newFromBuilder([
            'id' => 7,
            'type' => 4,
            'server' => '127.0.0.1',
            'push_port' => 443,
            'profile' => json_encode([]),
        ]);
        $node->setRelation('auth', (new NodeAuth)->newFromBuilder(['secret' => 'test-secret']));

        $this->node = $node;
    }

    protected function tearDown(): void
    {
        NodeReachability::clear(self::HOST);

        parent::tearDown();
    }

    public function test_a_backed_off_host_is_not_probed(): void
    {
        // 取不到集合的返回值不变，只是不再为一个连不上的地址烧满超时
        NodeReachability::mark(self::HOST);
        Http::fake(['*' => Http::response([['uid' => 11]])]);

        $this->assertFalse((new GetUser)->list($this->node));

        Http::assertNothingSent();
    }

    public function test_empty_user_list_is_not_a_failure(): void
    {
        Http::fake(['*' => Http::response([])]);

        $this->assertSame([], (new GetUser)->list($this->node));
    }

    public function test_uid_list_is_returned(): void
    {
        Http::fake(['*' => Http::response([['uid' => 11, 'port' => 10011], ['uid' => 12, 'port' => 10012]])]);

        $this->assertSame([11, 12], (new GetUser)->list($this->node));
    }

    public function test_error_envelope_is_a_failure_not_an_empty_list(): void
    {
        // secret 不匹配时节点仍回 200，但内容是错误信封
        Http::fake(['*' => Http::response(['success' => 'false', 'content' => 'secret check error'])]);

        $this->assertFalse((new GetUser)->list($this->node));
    }

    /** 节点把失败原因放在信封的 content 里，日志必须带出来，否则只剩「不是用户数组」无从排查 */
    public function test_error_envelope_reason_is_logged(): void
    {
        Http::fake(['*' => Http::response(['success' => 'false', 'content' => 'secret check error'])]);
        Log::spy();

        $this->assertFalse((new GetUser)->list($this->node));

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message): bool => str_contains($message, 'secret check error'))
            ->once();
    }

    public function test_non_2xx_is_a_failure(): void
    {
        Http::fake(['*' => Http::response('', 502)]);

        $this->assertFalse((new GetUser)->list($this->node));
    }

    public function test_transport_exception_is_a_failure(): void
    {
        Http::fake(['*' => fn () => throw new ConnectionException('connection refused')]);

        $this->assertFalse((new GetUser)->list($this->node));
    }
}
