<?php

namespace App\Observers;

use App\Jobs\VNet\reloadNode;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Models\RuleGroup;
use App\Models\UserGroup;
use App\Services\NodeService;
use Log;
use Str;

class NodeObserver {
	public function saved(Node $node): void {
		(new NodeService())->getNodeGeo($node->id);
	}

	public function created(Node $node): void {
		$auth = new NodeAuth();
		$auth->node_id = $node->id;
		$auth->key = Str::random();
		$auth->secret = Str::random(8);
		if(!$auth->save()){
			Log::error('节点生成-自动生成授权时出现错误，请稍后自行生成授权！');
		}
	}

	public function updated(Node $node): void {
		if($node->type == 4){
			reloadNode::dispatch(Node::whereType(4)->whereId($node->id)->get());
		}
	}

	public function deleted(Node $node): void {
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
		foreach(RuleGroup::all() as $ruleGroup){
			$nodes = $ruleGroup->nodes;
			if($nodes && in_array($node->id, $nodes, true)){
				$ruleGroup->nodes = array_merge(array_diff($nodes, [$node->id]));
				$ruleGroup->save();
			}
		}

		// 断开用户分组控制节点联系
		foreach(UserGroup::all() as $userGroup){
			$nodes = $userGroup->nodes;
			if($nodes && in_array($node->id, $nodes, true)){
				$userGroup->nodes = array_merge(array_diff($nodes, [$node->id]));
				$userGroup->save();
			}
		}
	}
}
