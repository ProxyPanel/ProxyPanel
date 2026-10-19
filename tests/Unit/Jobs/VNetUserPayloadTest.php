<?php

namespace Tests\Unit\Jobs;

use App\Jobs\VNet\AddUser;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Models\User;
use Exception;
use Http;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Log;
use ReflectionProperty;
use Tests\TestCase;

/**
 * VNet 的 UserInfo.speed_limit 是 uint64 且按字节/秒生效（service/limit.go 把它直接喂给 rate.Limit），
 * 所以下发的必须是原始字节数：既不能是 cast 出来的 Mbps 小数，也不能小 125000 倍。
 */
class VNetUserPayloadTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Node $node;

    protected function setUp(): void
    {
        parent::setUp();

        // 12.5 Mbps：按 Mbps 下发既不是整数（Go 绑不进 uint64），又比实际值小 125000 倍
        $this->user = User::create([
            'username' => 'vnet-payload-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => 10000 + random_int(1, 999),
            'speed_limit' => 12.5,
        ]);

        $this->node = new Node([
            'name' => 'vnet-test',
            'type' => 4,
            'server' => '127.0.0.1',
            'ip' => '127.0.0.1',
            'push_port' => 443,
            'profile' => [],
            'details' => [],
        ]);
        $this->node->id = 1;
        $this->node->exists = true;
        // 节点不入库，auth 关系直接给定：handle() 要用它拼 secret 头
        $this->node->setRelation('auth', (new NodeAuth)->newFromBuilder(['secret' => 'test-secret']));
    }

    public function test_add_user_sends_raw_bytes(): void
    {
        $rows = $this->dataOf(new AddUser([$this->user->id], $this->node));

        $this->assertCount(1, $rows);
        $this->assertSame(1562500, $rows[0]['speed_limit']);
    }

    public function test_unlimited_user_sends_zero(): void
    {
        $this->user->update(['speed_limit' => 0]);
        $fresh = User::find($this->user->id);

        $rows = $this->dataOf(new AddUser([$fresh->id], $this->node));

        // 0 是不限速，VNet 侧靠它跳过建限速器（limit.go 的 limition != 0 分支）
        $this->assertSame(0, $rows[0]['speed_limit']);
    }

    public function test_disabled_account_is_pushed_with_enable_zero(): void
    {
        // 节点按 enable 摘账号，所以禁用只需把状态推过去，不必由面板先判「该加还是该删」
        $this->user->update(['enable' => 0]);
        $fresh = User::find($this->user->id);

        $rows = $this->dataOf(new AddUser([$fresh->id], $this->node));

        $this->assertSame(0, $rows[0]['enable']);
    }

    public function test_transport_failure_is_rethrown_for_the_queue(): void
    {
        Http::fake(['*' => Http::response('', 502)]);

        // 没送达就抛：让 Horizon 按 $tries 重投，比只留一行 alert 日志强
        $this->expectException(Exception::class);

        (new AddUser([$this->user->id], $this->node))->handle();
    }

    public function test_explicit_refusal_is_logged_but_not_retried(): void
    {
        // 节点明确回绝（例如端口被占）重投也不会变，只记日志、不抛异常
        Http::fake(['*' => Http::response(['success' => 'false', 'content' => 'add user port 10001 is used by 9'])]);
        Log::shouldReceive('alert')->once();

        (new AddUser([$this->user->id], $this->node))->handle();
    }

    private function dataOf(object $job): array
    {
        $property = (new ReflectionProperty($job, 'data'));
        $property->setAccessible(true);

        return $property->getValue($job);
    }
}
