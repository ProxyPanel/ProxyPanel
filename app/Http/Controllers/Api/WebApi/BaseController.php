<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Models\Node;
use App\Models\NodeHeartBeat;
use App\Models\NodeOnlineLog;
use App\Models\NodeOnlineUserIp;
use App\Models\Rule;
use App\Models\RuleGroup;
use App\Models\RuleGroupNode;
use App\Models\RuleLog;
use App\Models\User;
use App\Models\UserDataFlowLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class BaseController {
	// 上报节点心跳信息
	public function setNodeStatus(Request $request, $id): JsonResponse {
		$cpu = (int) $request->input('cpu') / 100;
		$mem = (int) $request->input('mem') / 100;
		$disk = (int) $request->input('disk') / 100;

		if(is_null($request->input('uptime'))){
			return $this->returnData('上报节点心跳信息失败，请检查字段');
		}

		$obj = new NodeHeartBeat();
		$obj->node_id = $id;
		$obj->uptime = (int) $request->input('uptime');
		//$obj->load = $request->input('load');
		$obj->load = implode(' ', [$cpu, $mem, $disk]);
		$obj->log_time = time();
		$obj->save();

		if($obj->id){
			return $this->returnData('上报节点心跳信息成功', 'success', 200);
		}

		return $this->returnData('上报节点心跳信息失败，请检查字段');
	}

	// 返回数据
	public function returnData($message, $status = 'fail', $code = 400, $data = [], $addition = []): JsonResponse {
		$data = ['status' => $status, 'code' => $code, 'data' => $data, 'message' => $message];

		if($addition){
			$data = array_merge($data, $addition);
		}

		return Response::json($data);
	}

	// 上报节点在线人数
	public function setNodeOnline(Request $request, $id): JsonResponse {
		$inputArray = $request->all();
		$onlineCount = 0;
		foreach($inputArray as $input){
			if(!array_key_exists('ip', $input) || !array_key_exists('uid', $input)){
				return $this->returnData('上报节点在线情况失败，请检查字段');
			}

			if(!isset($input['ip'], $input['uid'])){
				return $this->returnData('上报节点在线情况失败，请检查字段');
			}

			$obj = new NodeOnlineUserIp();
			$obj->node_id = $id;
			$obj->user_id = $input['uid'];
			$obj->ip = $input['ip'];
			$obj->port = User::find($input['uid'])->port;
			$obj->created_at = time();
			$obj->save();

			if(!$obj->id){
				return $this->returnData('上报节点在线情况失败，请检查字段');
			}
			$onlineCount++;
		}

		$obj = new NodeOnlineLog();
		$obj->node_id = $id;
		$obj->online_user = $onlineCount;
		$obj->log_time = time();
		$obj->save();

		if($obj->id){
			return $this->returnData('上报节点在线情况成功', 'success', 200);
		}

		return $this->returnData('上报节点在线情况失败，请检查字段');
	}

	// 上报用户流量日志
	public function setUserTraffic(Request $request, $id): JsonResponse {
		foreach($request->all() as $input){
			if(!array_key_exists('uid', $input)){
				return $this->returnData('上报用户流量日志失败，请检查字段');
			}

			$rate = Node::find($id)->traffic_rate;

			$log = new UserDataFlowLog();
			$log->user_id = (int) $input['uid'];
			$log->u = (int) $input['upload'] * $rate;
			$log->d = (int) $input['download'] * $rate;
			$log->node_id = $id;
			$log->rate = $rate;
			$log->traffic = flowAutoShow($log->u + $log->d);
			$log->log_time = time();
			$log->save();

			if(!$log->id){
				return $this->returnData('上报用户流量日志失败，请检查字段');
			}
			$user = User::find($log->user_id);
			if($user){
				$user->u += $log->u;
				$user->d += $log->d;
				$user->t = time();
				$user->save();
			}
		}

		return $this->returnData('上报用户流量日志成功', 'success', 200);
	}

	// 获取节点的审计规则
	public function getNodeRule($id): JsonResponse {
		$nodeRule = RuleGroupNode::whereNodeId($id)->first();
		$data = [];
		//节点未设置任何审计规则
		if($nodeRule){
			$ruleGroup = RuleGroup::find($nodeRule->rule_group_id);
			if($ruleGroup && $ruleGroup->rules){
				foreach($ruleGroup->rules as $ruleId){
					$rule = Rule::find($ruleId);
					if($rule){
						$data[] = [
							'id'      => $rule->id,
							'type'    => $rule->type_api_label,
							'pattern' => $rule->pattern
						];
					}
				}

				return $this->returnData('获取节点审计规则成功', 'success', 200,
					['mode' => $ruleGroup->type? 'reject' : 'allow', 'rules' => $data]);
			}
		}

		//放行
		return $this->returnData('获取节点审计规则成功', 'success', 200, ['mode' => 'all', 'rules' => $data]);
	}

	// 上报用户触发的审计规则记录
	public function addRuleLog(Request $request, $id): JsonResponse {
		if($request->has(['uid', 'rule_id', 'reason'])){
			$obj = new RuleLog();
			$obj->user_id = $request->input('uid');
			$obj->node_id = $id;
			$obj->rule_id = $request->input('rule_id');
			$obj->reason = $request->input('reason');
			$obj->save();

			if($obj->id){
				return $this->returnData('上报用户触发审计规则记录成功', 'success', 200);
			}
		}

		return $this->returnData('上报用户触发审计规则记录失败');
	}
}
