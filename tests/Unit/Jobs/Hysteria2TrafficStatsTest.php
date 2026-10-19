<?php

namespace Tests\Unit\Jobs;

use App\Enums\ProxyType;
use App\Jobs\Hysteria2\GetTrafficStats;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Models\User;
use App\Models\UserDataFlowLog;
use DB;
use Http;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Hysteria2 流量采集：累加走数据库侧，同一账号的每份增量都要落。
 */
class Hysteria2TrafficStatsTest extends TestCase
{
    use DatabaseTransactions;

    private Node $node;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->node = Node::withoutEvents(fn () => Node::create([
            'name' => 'hy2-'.uniqid(),
            'type' => ProxyType::HYSTERIA2->value,
            'profile' => [],
            'server' => 'hy2-'.uniqid().'.test',
            'is_ddns' => 1,
            'push_port' => 9090,
            'traffic_rate' => 2,
        ]));
        NodeAuth::create(['node_id' => $this->node->id, 'key' => '0123456789abcdef', 'secret' => 's3cr3txy']);

        $this->user = User::create([
            'username' => 'hy2-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'transfer_enable' => 10 * GiB,
        ]);
    }

    public function test_accumulates_on_the_database_side(): void
    {
        $this->report([$this->user->id => ['tx' => 1000, 'rx' => 2000]]);

        DB::enableQueryLog();
        (new GetTrafficStats($this->node))->handle();
        $sql = implode(' ', array_column(DB::getQueryLog(), 'query'));
        DB::disableQueryLog();

        $this->assertStringContainsString('u = u + CASE', $sql, '累加必须留在库里，不能读回来加完再写回');
        $this->assertSame(2000, (int) User::find($this->user->id)->u);
        $this->assertSame(4000, (int) User::find($this->user->id)->d);
    }

    public function test_every_interval_for_the_same_account_lands(): void
    {
        // 多轮采集共用一个 fake：Http::fake() 是叠加的，先注册的兜底桩会一直命中
        $this->report(
            [$this->user->id => ['tx' => 100, 'rx' => 100]],
            [$this->user->id => ['tx' => 200, 'rx' => 300]],
        );

        (new GetTrafficStats($this->node))->handle();
        (new GetTrafficStats($this->node))->handle();

        $user = User::find($this->user->id);
        $this->assertSame(600, (int) $user->u);
        $this->assertSame(800, (int) $user->d);
    }

    public function test_writes_one_log_row_per_account_with_the_node_rate(): void
    {
        $this->report([$this->user->id => ['tx' => 100, 'rx' => 200]]);
        (new GetTrafficStats($this->node))->handle();

        $log = UserDataFlowLog::whereUserId($this->user->id)->whereNodeId($this->node->id)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame(200, (int) $log->u);
        $this->assertSame(400, (int) $log->d);
        $this->assertSame(2.0, round((float) $log->rate, 2));
    }

    public function test_a_revoked_account_in_the_report_does_not_cost_the_others(): void
    {
        $ghost = (int) User::max('id') + 9999; // 节点侧残留的已删账号

        $this->report([$ghost => ['tx' => 50, 'rx' => 50], $this->user->id => ['tx' => 100, 'rx' => 100]]);
        (new GetTrafficStats($this->node))->handle();

        $this->assertSame(200, (int) User::find($this->user->id)->u, '同批有效账号不能被残留ID连累');
        $this->assertSame(0, UserDataFlowLog::whereUserId($ghost)->count());
    }

    /** 日志表按 bigint 存字节增量：一轮超过 2 GiB 的上报不能被截断 */
    public function test_a_round_above_two_gigabytes_is_stored_whole(): void
    {
        $delta = 3 * GiB + 7;

        $this->report([$this->user->id => ['tx' => $delta, 'rx' => $delta]]);
        (new GetTrafficStats($this->node))->handle();

        $log = UserDataFlowLog::whereUserId($this->user->id)->latest('id')->first();
        $this->assertSame(2 * $delta, (int) $log->u);
        $this->assertSame(2 * $delta, (int) $log->d);
        $this->assertSame(2 * $delta, (int) User::find($this->user->id)->u);
    }

    public function test_node_without_a_secret_is_skipped_without_requesting(): void
    {
        NodeAuth::whereNodeId($this->node->id)->delete();
        $this->report([$this->user->id => ['tx' => 100, 'rx' => 100]]);

        (new GetTrafficStats($this->node->fresh()))->handle();

        $this->assertSame(0, (int) User::find($this->user->id)->u);
        Http::assertNothingSent();
    }

    /**
     * 按轮次给出 /traffic 的响应：最后一轮会被重复返回。
     *
     * @param  array<int, array<string|int, array{tx: int, rx: int}>>  $rounds
     */
    private function report(array ...$rounds): void
    {
        Http::fake(function () use (&$rounds) {
            return Http::response(count($rounds) > 1 ? array_shift($rounds) : $rounds[0]);
        });
    }
}
