<?php

namespace Tests\Unit\Jobs;

use App\Jobs\VNet\AddUser;
use App\Jobs\VNet\DelUser;
use App\Jobs\VNet\NodeReachability;
use App\Jobs\VNet\ReloadRule;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Models\User;
use Exception;
use Http;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Tests\TestCase;

/**
 * 面板推 VNET 节点的投递语义：只有传输层没送达才值得重投（抛异常让队列再来一次），
 * 节点明确回绝（success=false）重投也不会变，必须当成已送达。
 * 三份 Job 共用这一套判据，用例按 Job 各自钉一遍。
 */
class VNetPushDeliveryTest extends TestCase
{
    use DatabaseTransactions;

    /** 夹具节点的寻址地址：退避窗口存在 cache 里，不跟着 DatabaseTransactions 回滚，用例前后都要清 */
    private const HOST = '127.0.0.1:443';

    private Node $node;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        NodeReachability::clear(self::HOST);

        $this->user = User::withoutEvents(fn () => User::create([
            'username' => 'push-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => 20000 + random_int(1, 999),
            'enable' => 1,
            'status' => 1,
        ]));

        $node = new Node([
            'name' => 'vnet-push',
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
    }

    protected function tearDown(): void
    {
        // 可达性窗口存在 cache 里，不跟着事务回滚：留着会让后面任何推同一地址的用例都被跳过
        NodeReachability::clear(self::HOST);

        parent::tearDown();
    }

    public function test_a_transport_failure_puts_the_address_in_backoff(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 7: Failed to connect to 127.0.0.1 port 1080'));

        try {
            (new AddUser([$this->user->id], $this->node))->handle();
            $this->fail('传输层没送达必须抛异常让队列重投');
        } catch (Exception) {
        }

        $this->assertTrue(NodeReachability::unreachable(self::HOST));
    }

    public function test_a_backed_off_address_is_undelivered_without_sending_anything(): void
    {
        // 判据不变：退避只是不再为必然连不上的地址烧超时，地址仍算未送达，重投照旧
        NodeReachability::mark(self::HOST);
        Http::fake(fn () => Http::response(['success' => 'true', 'content' => 'ok']));

        try {
            (new DelUser([$this->user->id], $this->node))->handle();
            $this->fail('退避窗口内的地址必须仍算未送达');
        } catch (Exception $exception) {
            $this->assertStringContainsString('未送达', $exception->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_an_http_answer_never_starts_the_backoff(): void
    {
        // 只有传输层没成功才登记：502 是活的地址在出错，把它退避掉会连带挡住之后的正常投递
        Http::fake(fn () => Http::response('', 502));

        try {
            (new AddUser([$this->user->id], $this->node))->handle();
        } catch (Exception) {
        }

        $this->assertFalse(NodeReachability::unreachable(self::HOST));
    }

    public function test_the_last_retry_lands_after_the_backoff_window(): void
    {
        // 重投落在窗口里就是「一个字节都没发出去却烧掉一次 tries」：一次超时会必然产出一行 failed_jobs
        foreach ([AddUser::class, DelUser::class, ReloadRule::class] as $class) {
            $backoff = (new ReflectionClass($class))->getDefaultProperties()['backoff'];
            $hint = $class.'（当前：'.implode(',', (array) $backoff).'，窗口：'.NodeReachability::TTL.' 秒）';

            $this->assertIsArray($backoff, $class.' 的重投间隔不再是数组，最后一次尝试没有排在窗口之后');
            $this->assertTrue($this->lastRetryClearsWindow($backoff), '末位重投落在退避窗口里，那次尝试一个字节都发不出去却要烧掉一次 tries '.$hint);
            $this->assertLessThan(NodeReachability::TTL, (int) $backoff[0], '首次重投不该等满一个窗口：非 2xx 那类失败不登记窗口 '.$hint);
        }

        // 反向对照：判据必须认得出改之前的形状，否则上面那条 assertTrue 是空过
        $this->assertFalse($this->lastRetryClearsWindow([30]));
    }

    /** 末位重投是否排在退避窗口结束之后 */
    private function lastRetryClearsWindow(array $backoff): bool
    {
        return (int) last($backoff) >= NodeReachability::TTL;
    }

    public function test_a_refusal_to_delete_is_a_warning_and_not_an_alert(): void
    {
        // 节点上本来没这个人是删除的常态（掉级换组也要显式 DelUser），不该占告警
        Log::spy();
        Http::fake(fn () => Http::response(['success' => 'false', 'content' => 'uid 12 is not esixt']));

        (new DelUser([$this->user->id], $this->node))->handle();

        Log::shouldHaveReceived('warning');
        Log::shouldNotHaveReceived('alert');
    }

    public function test_a_refusal_to_add_still_raises_an_alert(): void
    {
        Log::spy();
        Http::fake(fn () => Http::response(['success' => 'false', 'content' => 'bad profile']));

        (new AddUser([$this->user->id], $this->node))->handle();

        Log::shouldHaveReceived('alert');
        Log::shouldNotHaveReceived('warning');
    }

    public function test_add_user_acks_when_the_node_accepts(): void
    {
        Http::fake(fn () => Http::response(['success' => 'true', 'content' => 'ok']));

        (new AddUser([$this->user->id], $this->node))->handle();

        $this->assertTrue(true);
    }

    public function test_add_user_treats_a_node_refusal_as_delivered(): void
    {
        Http::fake(fn () => Http::response(['success' => 'false', 'content' => 'refused']));

        (new AddUser([$this->user->id], $this->node))->handle();

        $this->assertTrue(true);
    }

    public function test_add_user_retries_when_the_response_is_not_json(): void
    {
        // 反代错误页：这次没送到，必须让队列重投而不是留一行日志
        Http::fake(fn () => Http::response('<html>502 Bad Gateway</html>', 502, ['Content-Type' => 'text/html']));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('未送达');

        (new AddUser([$this->user->id], $this->node))->handle();
    }

    public function test_add_user_retries_when_the_connection_fails(): void
    {
        Http::fake(fn () => throw new Exception('connection refused'));

        $this->expectException(Exception::class);

        (new AddUser([$this->user->id], $this->node))->handle();
    }

    public function test_del_user_posts_the_id_list_to_the_v2_endpoint(): void
    {
        Http::fake(fn () => Http::response(['success' => 'true']));
        (new DelUser([$this->user->id], $this->node))->handle();

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), 'api/v2/user/del/list')
            && $request->data() === [$this->user->id]);
    }

    public function test_del_user_still_uses_the_legacy_single_id_endpoint(): void
    {
        Http::fake(fn () => Http::response(['success' => 'true']));
        (new DelUser($this->user->id, $this->node))->handle();

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), 'api/user/del/'.$this->user->id));
    }

    public function test_del_user_treats_a_node_refusal_as_delivered(): void
    {
        // 本来就没这个人：重投也不会变，必须当成已送达
        Http::fake(fn () => Http::response(['success' => 'false', 'content' => 'no such user']));

        (new DelUser([$this->user->id], $this->node))->handle();

        $this->assertTrue(true);
    }

    public function test_del_user_retries_when_the_node_returns_an_error_page(): void
    {
        // 注意：一个用例只能装一次 Http::fake，重复调用是叠加桩而不是替换
        Http::fake(fn () => Http::response('', 500));

        $this->expectException(Exception::class);

        (new DelUser([$this->user->id], $this->node))->handle();
    }

    public function test_reload_rule_posts_without_a_body_and_acks(): void
    {
        Http::fake(fn () => Http::response(['success' => 'true']));

        (new ReloadRule($this->node))->handle();

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), 'api/v2/rule/reload'));
    }

    public function test_reload_rule_retries_when_the_node_is_unreachable(): void
    {
        Http::fake(fn () => throw new Exception('timed out'));

        $this->expectException(Exception::class);

        (new ReloadRule($this->node))->handle();
    }
}
