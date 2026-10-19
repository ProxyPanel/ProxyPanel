<?php

namespace Tests\Unit\Jobs;

use App\Jobs\VNet\AddUser;
use App\Jobs\VNet\DelUser;
use App\Jobs\VNet\ReconcileNode;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Models\User;
use Http;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Queue;
use Tests\TestCase;

/**
 * 对账必须「只按面板记着的集合补删」，且在拿不到节点集合时不动手：
 * 把「拉取失败」当成「节点上没人」会让整节点账号被当成缺漏重推，反之会把在线用户误删。
 *
 * 期望集合一律现取 Node::users()（作业用的同一个查询），不假设跑测试的库里有几个用户。
 */
class VNetReconcileTest extends TestCase
{
    use DatabaseTransactions;

    private Node $node;

    private array $expected;

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'username' => 'vnet-reconcile-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => 20000 + random_int(1, 999),
            'level' => 0,
            'enable' => 1,
            'status' => 1,
        ]);

        // 节点不入库：对账只读它的寻址字段与关联，Node::users() 查的是 user 表
        $node = new Node([
            'name' => 'vnet-reconcile',
            'type' => 4,
            'server' => '127.0.0.1',
            'is_ddns' => 0,
            'level' => 0,
            'push_port' => 443,
            'profile' => [],
        ]);
        $node->id = 3;
        $node->exists = true;
        $node->setRelation('auth', (new NodeAuth)->newFromBuilder(['secret' => 'test-secret']));

        $this->node = $node;
        $this->expected = array_map('intval', $node->users()->pluck('id')->all());
    }

    public function test_missing_accounts_are_pushed_when_node_reports_nobody(): void
    {
        Queue::fake();
        Http::fake(['*' => Http::response([])]);

        (new ReconcileNode($this->node))->handle();

        Queue::assertPushed(AddUser::class, fn (AddUser $job) => $this->sorted($this->expected) === $this->uidsOf($job));
        Queue::assertNotPushed(DelUser::class);
    }

    public function test_stale_accounts_are_removed_without_repushing_known_ones(): void
    {
        Queue::fake();
        Http::fake(['*' => Http::response(
            array_map(fn (int $uid) => ['uid' => $uid], array_merge($this->expected, [999999]))
        )]);

        (new ReconcileNode($this->node))->handle();

        Queue::assertNotPushed(AddUser::class);
        Queue::assertPushed(DelUser::class, fn (DelUser $job) => [999999] === $this->userIdsOf($job));
    }

    public function test_in_sync_node_gets_no_push(): void
    {
        Queue::fake();
        Http::fake(['*' => Http::response(array_map(fn (int $uid) => ['uid' => $uid], $this->expected))]);

        (new ReconcileNode($this->node))->handle();

        Queue::assertNotPushed(AddUser::class);
        Queue::assertNotPushed(DelUser::class);
    }

    public function test_unreachable_node_is_skipped_instead_of_treated_as_empty(): void
    {
        Queue::fake();
        Http::fake(['*' => Http::response('', 502)]);

        (new ReconcileNode($this->node))->handle();

        Queue::assertNotPushed(AddUser::class);
        Queue::assertNotPushed(DelUser::class);
    }

    public function test_each_address_of_a_multi_ip_node_is_checked_on_its_own(): void
    {
        $this->node->ip = '1.1.1.1,2.2.2.2';
        Queue::fake();
        Http::fake([
            '*1.1.1.1*' => Http::response([]), // 这个地址缺账号
            '*2.2.2.2*' => Http::response(array_map(fn (int $uid) => ['uid' => $uid], $this->expected)), // 这个地址是齐的
        ]);

        (new ReconcileNode($this->node))->handle();

        // 只有确实缺账号的那个地址会派发一次
        $this->assertSame(1, Queue::pushed(AddUser::class)->count());
        Queue::assertNotPushed(DelUser::class);
    }

    /** AddUser 在构造时按 uid 重新取用户，读它的 data 就是这批要补的人 */
    private function uidsOf(AddUser $job): array
    {
        $property = (new \ReflectionProperty($job, 'data'));
        $property->setAccessible(true);

        $uids = array_map(fn (array $row) => (int) $row['uid'], $property->getValue($job));
        sort($uids);

        return $uids;
    }

    private function sorted(array $uids): array
    {
        sort($uids);

        return $uids;
    }

    private function userIdsOf(DelUser $job): array
    {
        $property = (new \ReflectionProperty($job, 'userIds'));
        $property->setAccessible(true);

        return array_map('intval', (array) $property->getValue($job));
    }
}
