<?php

namespace App\Observers;

use App\Jobs\VNet\reloadNode;
use App\Models\Node;
use App\Models\RuleGroup;
use Arr;

class RuleGroupObserver
{
    public function updated(RuleGroup $ruleGroup): void
    {
        $changes = $ruleGroup->getChanges();
        if ($ruleGroup->nodes && Arr::hasAny($changes, ['type', 'rules'])) {
            $nodes = Node::whereType(4)->whereIn('id', $ruleGroup->nodes)->get();
            if ($nodes) {
                reloadNode::dispatchNow($nodes);
            }
        } elseif ($ruleGroup->rules && Arr::exists($changes, 'nodes')) {
            $arrayDiff = array_merge(
                array_diff($ruleGroup->nodes ?? [], $ruleGroup->getOriginal('nodes') ?? []),
                array_diff($ruleGroup->getOriginal('nodes') ?? [], $ruleGroup->nodes ?? [])
            );

            if ($arrayDiff) {
                $nodes = Node::whereType(4)->whereIn('id', $arrayDiff)->get();
                if ($nodes) {
                    reloadNode::dispatchNow($nodes);
                }
            }
        }
    }
}
