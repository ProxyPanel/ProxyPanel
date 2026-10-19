<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\Api\WebApi\CoreController;
use App\Models\Node;
use App\Models\NodeOnlineIp;
use App\Models\NodeOnlineLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * 节点在线上报的容错判据：失效账号只该让自己出局，不该让整批在线记录被拒收——
 * 节点（VNet-SSR 的 ReportOnline）每轮上报后就把本地在线表清空，被拒的那一分钟无法重放。
 */
class NodeOnlineReportTest extends TestCase
{
    use DatabaseTransactions;

    private Node $node;

    protected function setUp(): void
    {
        parent::setUp();

        $this->node = Node::withoutEvents(fn () => Node::create([
            'name' => 'online-'.uniqid(),
            'type' => 4,
            'profile' => [],
        ]));
    }

    public function test_stale_account_does_not_reject_the_whole_batch(): void
    {
        $kept = $this->user();
        $gone = $this->user();
        $gone->forceDelete();

        $response = $this->report([
            ['uid' => $kept->id, 'ip' => '1.2.3.4'],
            ['uid' => $gone->id, 'ip' => '5.6.7.8'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('success', $response->getData(true)['status']);

        $rows = NodeOnlineIp::whereNodeId($this->node->id)->get();
        $this->assertCount(1, $rows);
        $this->assertSame($kept->id, (int) $rows[0]->user_id);
        $this->assertSame((int) $kept->port, (int) $rows[0]->port);

        $this->assertSame(1, (int) NodeOnlineLog::whereNodeId($this->node->id)->latest('id')->first()->online_user);
    }

    public function test_report_of_only_stale_accounts_is_accepted_with_zero_online(): void
    {
        $gone = $this->user();
        $gone->forceDelete();

        $response = $this->report([['uid' => $gone->id, 'ip' => '1.2.3.4']]);

        $this->assertSame('success', $response->getData(true)['status']);
        $this->assertSame(0, (int) NodeOnlineLog::whereNodeId($this->node->id)->latest('id')->first()->online_user);
        $this->assertSame(0, NodeOnlineIp::whereNodeId($this->node->id)->count());
    }

    public function test_malformed_record_is_still_rejected(): void
    {
        $response = $this->report([['uid' => $this->user()->id]]); // 缺 ip

        $this->assertSame('fail', $response->getData(true)['status']);
        $this->assertSame(0, NodeOnlineLog::whereNodeId($this->node->id)->count());
    }

    public function test_resent_batch_with_same_report_id_is_not_written_twice(): void
    {
        $records = [['uid' => $this->user()->id, 'ip' => '1.2.3.4', 'report_id' => 'online-'.uniqid()]];

        $this->assertSame('success', $this->report($records)->getData(true)['status']);
        // 节点发送超时后会把同一批并回重投：面板已入账，只补 ack，不再产生第二行
        $this->assertSame('success', $this->report($records)->getData(true)['status']);

        $this->assertSame(1, NodeOnlineIp::whereNodeId($this->node->id)->count());
    }

    private function report(array $records)
    {
        $request = Request::create('/api/ssr/v1/nodeOnline/'.$this->node->id, 'POST', $records);

        return (new CoreController)->setNodeOnline($request, $this->node);
    }

    private function user(): User
    {
        return User::create([
            'username' => 'online-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => 30000 + random_int(1, 9999),
        ]);
    }
}
