<?php

namespace App\Jobs\VNet;

use App\Enums\ProxyType;
use App\Jobs\VNet\Concerns\PushesToNodes;
use App\Models\Node;
use App\Models\Rule;
use App\Models\RuleGroup;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

/**
 * 规则变更后让 VNET 节点重拉审计规则：只打 api/v2/rule/reload，不重建监听、不断在线连接。
 *
 * 节点侧没有周期轮询，这条推送就是规则的唯一增量通道；送达失败抛异常让队列重试（$tries 内），
 * 节点此刻不在跑也没关系——它启动时会自己拉一次全量规则。
 */
class ReloadRule implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use PushesToNodes;
    use Queueable;
    use SerializesModels;

    public $tries = 3;

    public $backoff = [30, NodeReachability::TTL];

    private Collection $nodes;

    public function __construct(Collection|Node $nodes)
    {
        $this->nodes = $nodes instanceof Collection ? $nodes : new Collection([$nodes]);
    }

    /** 规则被哪些 VNET 节点消费：它所属分组挂到的那些节点 */
    public static function forRule(Rule $rule): void
    {
        static::pushTo(static::nodesForRule($rule));
    }

    /** 分组被哪些 VNET 节点消费 */
    public static function forGroup(RuleGroup $group): void
    {
        static::forGroups([$group->id]);
    }

    /** 节点自己换了所属分组：它消费的规则集合随之改变，而非 VNET 类型没有这条通道 */
    public static function forNode(Node $node): void
    {
        if ($node->type === ProxyType::VNET->value) {
            static::pushTo(new Collection([$node]));
        }
    }

    public static function forGroups(array $groupIds): void
    {
        static::pushTo(static::nodesForGroups($groupIds));
    }

    /**
     * 「谁消费这些分组」的判据只允许有一份：VNET 类型 + 挂着这些分组。
     * 删除流程要在删除之前调用它取走目标（pivot 是 CASCADE、node.rule_group_id 是 SET NULL）。
     */
    public static function nodesForGroups(array $groupIds): Collection
    {
        if ($groupIds === []) {
            return new Collection;
        }

        return Node::query()->where('type', ProxyType::VNET->value)->whereIn('rule_group_id', $groupIds)->get();
    }

    public static function nodesForRule(Rule $rule): Collection
    {
        return static::nodesForGroups($rule->rule_groups()->pluck('rule_group_id')->all());
    }

    /** 空集不投递：没有节点消费时不该产生空推送 */
    public static function pushTo(Collection $nodes): void
    {
        if ($nodes->isNotEmpty()) {
            static::dispatch($nodes);
        }
    }

    public function handle(): void
    {
        // 只有传输层没送达才值得重投；节点明确回绝（此刻它规则拉取失败会自己留旧集）只记日志
        $undelivered = $this->pushToNodes($this->nodes, 'api/v2/rule/reload', '重载规则', null, 10);

        if ($undelivered !== []) {
            throw new Exception('【重载规则】未送达：'.implode(',', $undelivered));
        }
    }

    // 队列失败处理
    public function failed(Throwable $exception): void
    {
        Log::alert('【重载规则】推送异常：'.$exception->getMessage());
    }
}
