<?php

namespace App\Observers;

use App\Jobs\VNet\reloadNode;
use App\Models\Node;
use App\Models\UserGroup;
use Arr;

class UserGroupObserver {
	public function created(UserGroup $userGroup): void {
		$nodes = Node::whereType(4)->whereIn('id', $userGroup->nodes)->get();
		if($nodes){
			reloadNode::dispatchNow($nodes);
		}
	}

	public function updated(UserGroup $userGroup): void {
		$changes = $userGroup->getChanges();
		if(Arr::exists($changes, 'nodes')){
			$nodes = Node::whereType(4)
			             ->whereIn('id',
				             array_diff($userGroup->nodes, json_decode($userGroup->getOriginal('nodes'), true)?: []))
			             ->get();
			if($nodes){
				reloadNode::dispatchNow($nodes);
			}
		}
	}
}
