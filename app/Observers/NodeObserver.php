<?php

namespace App\Observers;

use App\Components\DDNS;
use App\Jobs\VNet\reloadNode;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Models\RuleGroup;
use App\Models\UserGroup;
use App\Services\NodeService;
use Arr;
use Log;
use Str;

class NodeObserver
{
    public function saved(Node $node): void
    {
        (new NodeService())->getNodeGeo($node->id);
    }

    public function created(Node $node): void
    {
        $auth = new NodeAuth();
        $auth->node_id = $node->id;
        $auth->key = Str::random();
        $auth->secret = Str::random(8);
        if (! $auth->save()) {
            Log::warning('节点生成-自动生成授权时出现错误，请稍后自行生成授权！');
        }

        if ($node->is_ddns == 0 && $node->server && sysConfig('ddns_mode')) {
            if ($node->ip) {
                DDNS::store($node->server, $node->ip);
            }
            if ($node->ipv6) {
                DDNS::store($node->server, $node->ipv6, 'AAAA');
            }
        }
    }

    public function updated(Node $node): void
    {
        if ($node->is_ddns == 0 && sysConfig('ddns_mode')) {
            $changes = $node->getChanges();
            if (Arr::hasAny($changes, ['ip', 'ipv6', 'server'])) {
                if (Arr::exists($changes, 'server')) {
                    DDNS::destroy($node->getOriginal('server'));
                    if ($node->ip) {
                        DDNS::store($node->server, $node->ip);
                    }
                    if ($node->ipv6) {
                        DDNS::store($node->server, $node->ipv6, 'AAAA');
                    }
                } else {
                    if (Arr::exists($changes, 'ip')) {
                        if ($node->ip && $node->getOriginal('ip')) {
                            DDNS::update($node->server, $node->ip);
                        } elseif ($node->ip) {
                            DDNS::store($node->server, $node->ip);
                        } else {
                            DDNS::destroy($node->server, 'A');
                        }
                    }
                    if (Arr::exists($changes, 'ipv6')) {
                        if ($node->ipv6 && $node->getOriginal('ipv6')) {
                            DDNS::update($node->server, $node->ipv6, 'AAAA');
                        } elseif ($node->ipv6) {
                            DDNS::store($node->server, $node->ipv6, 'AAAA');
                        } else {
                            DDNS::destroy($node->server, 'AAAA');
                        }
                    }
                }
            }
        }

        if ($node->type == 4) {
            reloadNode::dispatchNow(Node::whereId($node->id)->get());
        }
    }

    public function deleted(Node $node): void
    {
        // 删除分组关联、节点标签、节点相关日志
        $node->labels()->delete();
        $node->heartBeats()->delete();
        $node->onlineLogs()->delete();
        $node->pingLogs()->delete();
        $node->dailyDataFlows()->delete();
        $node->hourlyDataFlows()->delete();
        $node->rules()->delete();
        $node->ruleGroup()->delete();
        $node->auth()->delete();

        // 断开审计规则分组节点联系
        foreach (RuleGroup::all() as $ruleGroup) {
            $nodes = $ruleGroup->nodes;
            if ($nodes && in_array($node->id, $nodes, true)) {
                $ruleGroup->nodes = array_merge(array_diff($nodes, [$node->id]));
                $ruleGroup->save();
            }
        }

        // 断开用户分组控制节点联系
        foreach (UserGroup::all() as $userGroup) {
            $nodes = $userGroup->nodes;
            if ($nodes && in_array($node->id, $nodes, true)) {
                $userGroup->nodes = array_merge(array_diff($nodes, [$node->id]));
                $userGroup->save();
            }
        }

        if ($node->is_ddns == 0 && $node->server && sysConfig('ddns_mode')) {
            DDNS::destroy($node->server);
        }
    }
}
