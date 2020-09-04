<?php

namespace App\Observers;

use App\Jobs\VNet\reloadNode;
use App\Models\Node;
use App\Models\RuleGroup;
use Arr;

class RuleGroupObserver {
	public function updated(RuleGroup $ruleGroup): void {
		$changes = $ruleGroup->getChanges();
		if(Arr::exists($changes, 'type') || Arr::exists($changes, 'rules')){
			$nodes = Node::whereType(4)->whereIn('id', $ruleGroup->nodes)->get();
			if($nodes){
				reloadNode::dispatchNow($nodes);
			}
		}elseif(Arr::exists($changes, 'nodes')){
			$nodes = Node::whereType(4)
			             ->whereIn('id',
				             array_diff($ruleGroup->nodes, $ruleGroup->getOriginal('nodes')?: []))
			             ->get();
			if($nodes){
				reloadNode::dispatchNow($nodes);
			}
		}
	}
}
