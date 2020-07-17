<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Models\Rule;
use App\Models\RuleGroup;
use App\Models\RuleGroupNode;
use App\Models\RuleLog;
use App\Models\SsNode;
use App\Models\SsNodeInfo;
use App\Models\SsNodeIp;
use App\Models\SsNodeOnlineLog;
use App\Models\User;
use App\Models\UserTrafficLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class BaseController {
	// 上报节点心跳信息
	public function setNodeStatus(Request $request, $id): JsonResponse {
		$cpu = intval($request->input('cpu')) / 100;
		$mem = intval($request->input('mem')) / 100;
		$disk = intval($request->input('disk')) / 100;

		if(is_null($request->input('uptime'))){
			return $this->returnData('上报节点心跳信息失败，请检查字段');
		}

		$obj = new SsNodeInfo();
		$obj->node_id = $id;
		$obj->uptime = intval($request->input('uptime'));
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
	public function returnData($message, $status = 'fail', $code = 400, $data = '', $addition = false): JsonResponse {
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

			$obj = new SsNodeIp();
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

		$obj = new SsNodeOnlineLog();
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
		$inputArray = $request->all();

		foreach($inputArray as $input){
			if(!array_key_exists('uid', $input)){
				return $this->returnData('上报用户流量日志失败，请检查字段');
			}

			$rate = SsNode::find($id)->traffic_rate;

			$obj = new UserTrafficLog();
			$obj->user_id = intval($input['uid']);
			$obj->u = intval($input['upload']) * $rate;
			$obj->d = intval($input['download']) * $rate;
			$obj->node_id = $id;
			$obj->rate = $rate;
			$obj->traffic = flowAutoShow($obj->u + $obj->d);
			$obj->log_time = time();
			$obj->save();

			if(!$obj->id){
				return $this->returnData('上报用户流量日志失败，请检查字段');
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
			$ruleGroup = RuleGroup::query()->whereId($nodeRule->rule_group_id)->first();
			if($ruleGroup){
				$rules = explode(',', $ruleGroup->rules);
				foreach($rules as $ruleId){
					$rule = Rule::query()->whereId($ruleId)->first();
					if($rule){
						$new = [
							'id'      => $rule->id,
							'type'    => $rule->type_api_label,
							'pattern' => $rule->pattern
						];
						$data[] = $new;
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
