<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\Api\WebApi\CoreController;
use App\Models\Node;
use App\Models\User;
use App\Models\UserDataFlowLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * 节点流量上报的入账判据。
 *
 * VNet-SSR 2.2+ 发不出去就整批原样重投（report_id 不变），且要等这批投递成功才取新快照：
 * 面板对同一批重复累加就是重复计费，而对无法入账的批次回 fail 会把该节点的流量账冻住。
 * 不带 report_id 的上报（SS/SSR/V2Ray/Trojan 与 Hysteria2 拉取）必须保持逐次累加。
 */
class NodeTrafficReportTest extends TestCase
{
    use DatabaseTransactions;

    private Node $node;

    protected function setUp(): void
    {
        parent::setUp();

        // traffic_rate 要显式给：不填时模型属性为 null，倍率变成 0 会让所有金额断言恒真
        $this->node = Node::withoutEvents(fn () => Node::create([
            'name' => 'traffic-'.uniqid(),
            'type' => 4,
            'profile' => [],
            'traffic_rate' => 1,
        ]));
    }

    public function test_resent_batch_with_same_report_id_is_counted_once(): void
    {
        $user = $this->user();
        // report_id 必须每次跑都不同：认领键是 (node, report_id)，库重启后自增 id 会复用，同小时内重跑会把首批当成重投
        $records = [['uid' => $user->id, 'upload' => 100, 'download' => 200, 'report_id' => 'same-'.uniqid()]];

        $this->assertSame('success', $this->report($records)->getData(true)['status']);
        $this->assertSame('success', $this->report($records)->getData(true)['status']); // 面板已入账、响应丢失后的重投

        $user->refresh();
        $this->assertSame(100, (int) $user->u);
        $this->assertSame(200, (int) $user->d);
        $this->assertSame(1, UserDataFlowLog::whereNodeId($this->node->id)->count());
    }

    public function test_batch_with_a_new_report_id_is_counted_again(): void
    {
        $user = $this->user();

        $this->report([['uid' => $user->id, 'upload' => 100, 'download' => 200, 'report_id' => 'first-'.uniqid()]]);
        $this->report([['uid' => $user->id, 'upload' => 50, 'download' => 60, 'report_id' => 'second-'.uniqid()]]);

        $user->refresh();
        $this->assertSame(150, (int) $user->u);
        $this->assertSame(260, (int) $user->d);
        $this->assertSame(2, UserDataFlowLog::whereNodeId($this->node->id)->count());
    }

    public function test_report_without_report_id_is_never_deduped(): void
    {
        $user = $this->user();
        $records = [['uid' => $user->id, 'upload' => 100, 'download' => 200]];

        $this->report($records);
        $this->report($records);

        $user->refresh();
        $this->assertSame(200, (int) $user->u);
        $this->assertSame(400, (int) $user->d);
        $this->assertSame(2, UserDataFlowLog::whereNodeId($this->node->id)->count());
    }

    public function test_batch_of_only_deleted_accounts_is_acked(): void
    {
        $gone = $this->user();
        $gone->forceDelete();

        $response = $this->report([['uid' => $gone->id, 'upload' => 100, 'download' => 200, 'report_id' => '7-1700000000-6']]);

        $this->assertSame('success', $response->getData(true)['status']);
        $this->assertSame(0, UserDataFlowLog::whereNodeId($this->node->id)->count());
    }

    public function test_empty_batch_is_acked(): void
    {
        $response = $this->report([]);

        $this->assertSame('success', $response->getData(true)['status']);
        $this->assertSame(0, UserDataFlowLog::whereNodeId($this->node->id)->count());
    }

    public function test_malformed_record_is_still_rejected(): void
    {
        $response = $this->report([['uid' => $this->user()->id, 'upload' => 100]]); // 缺 download

        $this->assertSame('fail', $response->getData(true)['status']);
        $this->assertSame(0, UserDataFlowLog::whereNodeId($this->node->id)->count());
    }

    private function report(array $records)
    {
        $request = Request::create('/api/ssr/v1/userTraffic/'.$this->node->id, 'POST', $records);

        return (new CoreController)->setUserTraffic($request, $this->node);
    }

    private function user(): User
    {
        return User::create([
            'username' => 'traffic-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => 30000 + random_int(1, 9999),
        ]);
    }
}
