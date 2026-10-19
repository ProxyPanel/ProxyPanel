<?php

namespace Tests\Unit\Http;

use App\Enums\ProxyType;
use App\Http\Controllers\Api\WebApi\CoreController;
use App\Jobs\Hysteria2\GetOnlineUser;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Models\NodeHeartbeat;
use App\Utils\NodeTraffic\LockRetry;
use DB;
use Http;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PDOException;
use Tests\TestCase;

/**
 * 节点心跳写入的死锁处置。
 *
 * 两处心跳写入（节点上报 nodeStatus 与 Hysteria2 轮询观测）都是「每节点一行」的 upsert：同一次上报重跑
 * 只会把同一行刷成同一组值，所以死锁按 LockRetry 的判据重跑是安全的。注入点取 Connection::beforeExecuting，
 * 语句还没执行就被判死锁，重跑才算真的补写而不是写了两次。
 */
class NodeHeartbeatReportTest extends TestCase
{
    use DatabaseTransactions;

    private const DEADLOCK = 'SQLSTATE[HY000]: 1213 Deadlock found when trying to get lock; try restarting transaction';

    private Node $node;

    /** 还要注入几次死锁；0 即注入器静默 */
    private int $deadlocksLeft = 0;

    /** 已注入次数，用来反证重跑次数正好是判据规定的那几次 */
    private int $injections = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->node = Node::withoutEvents(fn () => Node::create([
            'name' => 'heartbeat-'.uniqid(),
            'type' => ProxyType::VNET->value,
            'profile' => [],
        ]));

        DB::connection()->beforeExecuting(function ($query) {
            if ($this->deadlocksLeft <= 0 || ! $this->isHeartbeatWrite($query)) {
                return;
            }

            $this->deadlocksLeft--;
            $this->injections++;

            throw new PDOException(self::DEADLOCK);
        });
    }

    public function test_each_report_refreshes_the_same_heartbeat_row(): void
    {
        $this->assertSame('success', $this->report(100)->getData(true)['status']);
        $this->assertSame('success', $this->report(200)->getData(true)['status']);

        $this->assertSame(1, NodeHeartbeat::where('node_id', $this->node->id)->count());
        $this->assertSame(200, (int) NodeHeartbeat::where('node_id', $this->node->id)->value('uptime'));
    }

    public function test_heartbeat_write_is_rerun_once_after_a_deadlock(): void
    {
        $this->deadlocksLeft = 1;

        $this->assertSame('success', $this->report()->getData(true)['status']);
        $this->assertSame(1, $this->injections);
        $this->assertSame(1, NodeHeartbeat::where('node_id', $this->node->id)->count());
    }

    public function test_exhausted_deadlock_is_not_reported_as_success(): void
    {
        $this->deadlocksLeft = LockRetry::ATTEMPTS;

        try {
            $this->report();
            $this->fail('死锁重跑用尽后要把异常交回调用方，不能把没写成的心跳报成成功');
        } catch (PDOException $exception) {
            $this->assertSame(self::DEADLOCK, $exception->getMessage());
        }

        $this->assertSame(LockRetry::ATTEMPTS, $this->injections);
        $this->assertSame(0, NodeHeartbeat::where('node_id', $this->node->id)->count());
    }

    public function test_hysteria2_observation_heartbeat_is_rerun_once_after_a_deadlock(): void
    {
        $node = $this->hy2Node();
        Http::fake(fn () => Http::response([]));
        $this->deadlocksLeft = 1;

        (new GetOnlineUser($node))->handle();

        $this->assertSame(1, $this->injections);
        $this->assertSame(1, NodeHeartbeat::where('node_id', $node->id)->count());
    }

    private function isHeartbeatWrite(string $query): bool
    {
        return str_contains($query, 'node_heartbeat') && str_contains(strtolower($query), 'insert');
    }

    private function report(int $uptime = 130164): JsonResponse
    {
        // cpu=0 是合法填写值：load 由 cpu/mem/disk 各除 100 拼成，节点上报的 0 不能被判成没填
        $request = Request::create('/api/ssr/v1/nodeStatus/'.$this->node->id, 'POST', [
            'cpu' => 0,
            'mem' => 13,
            'disk' => 34,
            'uptime' => $uptime,
        ]);

        return (new CoreController)->setNodeStatus($request, $this->node);
    }

    private function hy2Node(): Node
    {
        $node = Node::withoutEvents(fn () => Node::create([
            'name' => 'heartbeat-h2-'.uniqid(),
            'type' => ProxyType::HYSTERIA2->value,
            'profile' => [],
            'level' => 0,
            'ip' => '10.9.0.1',
            'push_port' => 8080,
            'is_ddns' => 0,
        ]));
        NodeAuth::create(['node_id' => $node->id, 'key' => 'abcdef0123456789', 'secret' => 'abcd1234']);

        return $node;
    }
}
