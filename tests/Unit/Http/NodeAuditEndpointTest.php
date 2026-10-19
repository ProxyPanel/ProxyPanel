<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\Api\WebApi\CoreController;
use App\Models\Node;
use App\Models\Rule;
use App\Models\RuleLog;
use App\Models\User;
use App\Notifications\Custom;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * 审计端点的形状判据：trigger 是排队重投（at-least-once），面板按冷却窗口只入账一次；
 * 白名单模式没有对应规则，rule_id 归 NULL 也必须留档；嗅探现场（去向/协议/域名/路径）进独立列。
 */
class NodeAuditEndpointTest extends TestCase
{
    use DatabaseTransactions;

    private Node $node;

    protected function setUp(): void
    {
        parent::setUp();

        $this->node = Node::withoutEvents(fn () => Node::create([
            'name' => 'audit-'.uniqid(),
            'type' => 4,
            'profile' => [],
        ]));
    }

    public function test_trigger_retry_is_recorded_only_once(): void
    {
        $user = $this->user();
        $rule = Rule::create(['name' => 'r-'.uniqid(), 'type' => 2, 'pattern' => 'evil.example.com']);

        $first = $this->trigger($user->id, $rule->id, ['reason' => 'evil.example.com']);
        $this->assertSame('success', $first->getData(true)['status']);

        // 冷却窗口内的重复投递：补 ack 但绝不写第二条
        $second = $this->trigger($user->id, $rule->id, ['reason' => 'evil.example.com']);
        $this->assertSame('success', $second->getData(true)['status']);
        $this->assertSame(1, RuleLog::where('user_id', $user->id)->where('reason', 'evil.example.com')->count());
    }

    public function test_whitelist_mode_trigger_with_zero_rule_id_is_recorded(): void
    {
        $user = $this->user();

        // allow 模式未命中时节点上报 rule_id=0，FK 只认存在的 id 或 NULL，落档归 NULL
        $response = $this->trigger($user->id, 0, ['reason' => 'not-in-allow-list.example']);

        $this->assertSame('success', $response->getData(true)['status']);
        $this->assertSame(1, RuleLog::where('user_id', $user->id)->whereNull('rule_id')->count());
    }

    public function test_audit_context_lands_in_dedicated_columns(): void
    {
        $user = $this->user();

        $response = $this->trigger($user->id, 0, [
            'reason' => 'a.example',
            'target' => '93.184.216.34:443',
            'sniff_protocol' => 'tls',
            'sniff_host' => 'a.example',
        ]);
        $this->assertSame('success', $response->getData(true)['status']);

        $log = RuleLog::where('user_id', $user->id)->first();
        $this->assertSame('93.184.216.34:443', $log->target);
        $this->assertSame('tls', $log->sniff_protocol);
        $this->assertSame('a.example', $log->sniff_host);

        // 坏协议值整条拒收，不静默入库
        $bad = $this->trigger($user->id, 0, ['reason' => 'x.example', 'sniff_protocol' => 'socks5']);
        $this->assertSame('fail', $bad->getData(true)['status']);
    }

    public function test_trigger_of_deleted_user_is_acked_without_a_row(): void
    {
        // 账号在节点排队那一秒被删是正常竞态：没有可挂的记录、也没有可封的人。
        // 回 fail 会让节点重投同一条（节点现在按永久拒绝丢掉它并留一行 error 日志），
        // 而「没账可记也要 ack」与流量上报是同一判据
        $response = $this->trigger(99999999, 0, ['reason' => 'x.example']);

        $this->assertSame('success', $response->getData(true)['status']);
        $this->assertSame(0, RuleLog::where('user_id', 99999999)->count());
    }

    public function test_threshold_ban_disables_user_and_notifies_admin(): void
    {
        config(['settings.rule_ban_times' => 2, 'settings.ban_duration' => 60]);
        Queue::fake(); // 封禁会触发用户下发链，隔掉真实推送
        Notification::fake();

        // 封禁提醒的收件人固定是 id=1 的管理员，空库上先造出来（DatabaseTransactions 会回滚）
        $admin = $this->admin();

        $user = $this->user();
        $this->trigger($user->id, 0, ['reason' => 'repeat.example']);
        $this->assertSame(1, (int) $user->fresh()->enable);

        // 第二次触发达阈值：封禁 + 通知管理员；同 (rule,reason) 处于记录冷却不影响计数
        $this->trigger($user->id, 0, ['reason' => 'repeat.example']);
        $banned = $user->fresh();
        $this->assertSame(0, (int) $banned->enable);
        $this->assertNotNull($banned->ban_time);
        Notification::assertSentTo($admin, Custom::class);
    }

    public function test_zero_threshold_only_records_without_ban(): void
    {
        config(['settings.rule_ban_times' => 0]);

        $user = $this->user();
        for ($i = 0; $i < 3; $i++) {
            $this->trigger($user->id, 0, ['reason' => 'quiet.example']); // 冷却窗口内只留一档，也不该封禁
        }

        $this->assertSame(1, (int) $user->fresh()->enable);
        $this->assertSame(1, RuleLog::where('user_id', $user->id)->where('reason', 'quiet.example')->count());
    }

    public function test_failed_write_releases_the_claim_so_the_retry_lands(): void
    {
        $user = $this->user();
        $boom = true;
        RuleLog::creating(function () use (&$boom) {
            if ($boom) {
                throw new Exception('写入失败注入');
            }
        });

        $this->assertSame('fail', $this->trigger($user->id, 0, ['reason' => 'retry.example'])->getData(true)['status']);

        // 节点排队重投同一份：认领没被放开的话这一条会被当成已记过而永久丢失
        $boom = false;
        $this->assertSame('success', $this->trigger($user->id, 0, ['reason' => 'retry.example'])->getData(true)['status']);
        $this->assertSame(1, RuleLog::where('user_id', $user->id)->where('reason', 'retry.example')->count());
    }

    private function trigger(int $uid, int $ruleId, array $context)
    {
        $request = Request::create('/api/ssr/v1/trigger/'.$this->node->id, 'POST', array_merge([
            'uid' => $uid, 'rule_id' => $ruleId,
        ], $context));

        return (new CoreController)->addRuleLog($request, $this->node);
    }

    private function user(): User
    {
        return User::create([
            'username' => 'audit-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => 30000 + random_int(1, 9999),
        ]);
    }
}
