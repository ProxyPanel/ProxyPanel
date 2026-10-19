<?php

namespace Tests\Unit\Jobs;

use App\Enums\ProxyType;
use App\Jobs\VNet\ReloadRule;
use App\Models\Node;
use App\Models\Rule;
use App\Models\RuleGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use ReflectionProperty;
use Tests\TestCase;

/**
 * 规则下发对象的判据只允许有一份：谁消费这条规则 = 它的分组挂到的那些 VNET 节点。
 * SSR 节点走另一套后端，永不是推送对象；删除流程必须在删除前把目标取走
 * （pivot 是 CASCADE、node.rule_group_id 是 SET NULL，删完再查就查不到了）。
 */
class ReloadRuleTargetingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->superAdmin();

        Queue::fake();
    }

    public function test_deleting_a_group_still_pushes_to_the_node_that_had_it(): void
    {
        ['group' => $group, 'vnet' => $vnet, 'ssr' => $ssr] = $this->fixture();

        $this->actingAs($this->admin)->delete(route('admin.rule.group.destroy', $group));

        Queue::assertPushed(ReloadRule::class, 1);
        $this->assertSame([$vnet->id], $this->pushedNodeIds());
    }

    public function test_deleting_a_rule_pushes_to_every_node_consuming_its_group(): void
    {
        ['group' => $group, 'rule' => $rule, 'vnet' => $vnet, 'ssr' => $ssr] = $this->fixture();

        $second = Node::withoutEvents(fn () => Node::create([
            'name' => 'vnet-'.uniqid(),
            'type' => ProxyType::VNET->value,
            'rule_group_id' => $group->id,
            'profile' => [],
        ]));

        $this->actingAs($this->admin)->delete(route('admin.rule.destroy', $rule));

        Queue::assertPushed(ReloadRule::class, 1);
        $this->assertEqualsCanonicalizing(
            [$vnet->id, $second->id],
            $this->pushedNodeIds(),
            'SSR 节点不该收到规则重载，消费该分组的全部 VNET 节点一个都不能漏'
        );
    }

    public function test_a_rule_in_no_group_pushes_to_nobody(): void
    {
        $rule = Rule::create(['name' => 'r-'.uniqid(), 'type' => 2, 'pattern' => 'a.example']);

        $this->actingAs($this->admin)->delete(route('admin.rule.destroy', $rule));

        Queue::assertPushed(ReloadRule::class, 0);
    }

    public function test_a_node_changing_its_rule_group_pushes_a_reload(): void
    {
        ['vnet' => $vnet, 'ssr' => $ssr] = $this->fixture();

        ReloadRule::forNode($vnet);
        $this->assertSame([$vnet->id], $this->pushedNodeIds(), '节点换分组=消费的规则集合变了，要让它重拉');

        ReloadRule::forNode($ssr);
        Queue::assertPushed(ReloadRule::class, 1); // SSR 走另一套后端，不是这条通道的对象
    }

    private function fixture(): array
    {
        $group = RuleGroup::create(['name' => 'g-'.uniqid(), 'type' => 1]);
        $rule = Rule::create(['name' => 'r-'.uniqid(), 'type' => 2, 'pattern' => 'a.example']);
        $rule->rule_groups()->attach($group);

        $make = fn (int $type) => Node::withoutEvents(fn () => Node::create([
            'name' => 'node-'.uniqid(),
            'type' => $type,
            'rule_group_id' => $group->id,
            'profile' => [],
        ]));

        return [
            'group' => $group,
            'rule' => $rule,
            'vnet' => $make(ProxyType::VNET->value),
            'ssr' => $make(ProxyType::SHADOWSOCKSR->value),
        ];
    }

    /** ReloadRule 的节点集合是私有的，用例只从投递出去的那份实例里读 */
    private function pushedNodeIds(): array
    {
        $pushed = Queue::pushedJobs()[ReloadRule::class][0]['job'];
        $nodes = (new ReflectionProperty(ReloadRule::class, 'nodes'))->getValue($pushed);

        return collect($nodes)->pluck('id')->all();
    }
}
