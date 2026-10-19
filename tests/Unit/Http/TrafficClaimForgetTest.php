<?php

namespace Tests\Unit\Http;

use App\Enums\ProxyType;
use App\Http\Controllers\Api\WebApi\CoreController;
use App\Models\Node;
use App\Models\User;
use App\Models\UserDataFlowLog;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * setUserTraffic 写库失败必须 forget 掉 (node, report_id) 认领：
 * 否则节点重投的同一批会被当成已记过而永久丢账；写成功时认领保留，重投只补 ack 不重复入账。
 */
class TrafficClaimForgetTest extends TestCase
{
    use DatabaseTransactions;

    private Node $node;

    protected function setUp(): void
    {
        parent::setUp();

        // traffic_rate 要显式给：null 会让倍率变 0，金额断言恒真
        $this->node = $this->makeNode();
    }

    public function test_failed_write_releases_the_claim_and_resend_lands(): void
    {
        $user = $this->user();
        $reportId = 'forget-'.uniqid();
        $records = [['uid' => $user->id, 'upload' => 100, 'download' => 200, 'report_id' => $reportId]];

        // 摘掉节点行：user_traffic_log.node_id 有外键，事务里的 INSERT 必然抛错（写失败注入点）
        DB::table('node')->where('id', $this->node->id)->delete();

        $response = $this->report($records);

        $this->assertSame('fail', $response->getData(true)['status']);
        $this->assertSame('生成用户流量日志失败', $response->getData(true)['message']);
        $this->assertFalse(cache()->has($this->claimKey($reportId)), '写失败必须放开认领');
        $this->assertSame(0, (int) $user->fresh()->u, '事务必须整体回滚，不能记半批');
        $this->assertSame(0, UserDataFlowLog::whereUserId($user->id)->count());

        $this->makeNode($this->node->id, $this->node->name); // 补回同一 id 的节点行，模拟故障恢复

        // 同一批重投：认领已放开，这次必须真正入账，而不是被当成已记过
        $retry = $this->report($records);

        $this->assertSame('success', $retry->getData(true)['status']);
        $this->assertSame(100, (int) $user->fresh()->u);
        $this->assertSame(200, (int) $user->fresh()->d);
        $this->assertSame(1, UserDataFlowLog::whereUserId($user->id)->count());
    }

    public function test_successful_write_keeps_the_claim_and_resend_is_only_acked(): void
    {
        $user = $this->user();
        $reportId = 'keep-'.uniqid();
        $records = [['uid' => $user->id, 'upload' => 100, 'download' => 200, 'report_id' => $reportId]];

        $this->assertSame('success', $this->report($records)->getData(true)['status']);
        $this->assertTrue(cache()->has($this->claimKey($reportId)));

        // 面板已入账、响应丢失后的重投：补 ack 但绝不重复累加
        $this->assertSame('success', $this->report($records)->getData(true)['status']);
        $this->assertSame(100, (int) $user->fresh()->u);
        $this->assertSame(200, (int) $user->fresh()->d);
        $this->assertSame(1, UserDataFlowLog::whereUserId($user->id)->count());
    }

    private function report(array $records)
    {
        $request = Request::create('/api/ssr/v1/userTraffic/'.$this->node->id, 'POST', $records);

        return (new CoreController)->setUserTraffic($request, $this->node);
    }

    private function claimKey(string $reportId): string
    {
        return 'nodeTrafficReport_'.$this->node->id.'_'.$reportId;
    }

    private function makeNode(?int $id = null, ?string $name = null): Node
    {
        $attributes = [
            'name' => $name ?? 'claim-'.uniqid(),
            'type' => ProxyType::VNET->value,
            'profile' => [],
            'traffic_rate' => 1,
        ];
        if ($id !== null) {
            $attributes['id'] = $id;
        }

        return Node::withoutEvents(fn () => Node::create($attributes));
    }

    private function user(): User
    {
        return User::create([
            'username' => 'claim-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => 30000 + random_int(1, 9999),
        ]);
    }
}
