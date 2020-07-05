<?php

namespace App\Http\Controllers\Api\V2Ray;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Models\NodeCertificate;
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
use Illuminate\Http\Request;
use Response;

class V1Controller extends Controller {
	// 获取节点信息
	public function getNodeInfo($id) {
		$node = SsNode::query()->whereId($id)->first();
		$nodeTls = NodeCertificate::query()->whereId($node->server)->first();
		return Response::json([
			'status'  => 'success',
			'code'    => 200,
			'data'    => [
				'id'              => $node->id,
				'is_udp'          => $node->is_udp,
				'client_limit'    => $node->client_limit,
				'push_port'       => $node->push_port,
				'secret'          => $node->auth->secret,
				'key'             => $nodeTls? $nodeTls->key : '',
				'pem'             => $nodeTls? $nodeTls->pem : '',
				'v2_license'      => Helpers::systemConfig()['v2ray_license'],
				'v2_alter_id'     => $node->v2_alter_id,
				'v2_port'         => $node->v2_port,
				'v2_method'       => $node->v2_method,
				'v2_net'          => $node->v2_net,
				'v2_type'         => $node->v2_type,
				'v2_host'         => $node->v2_host,
				'v2_path'         => $node->v2_path,
				'v2_tls'          => $node->v2_tls,
				'v2_tls_provider' => $node->v2_tls_provider,
			],
			'message' => '获取节点信息成功'
		]);
	}

	// 上报节点心跳信息
	public function setNodeStatus(Request $request, $id) {
		$cpu = intval($request->input('cpu')) / 100;
		$mem = intval($request->input('mem')) / 100;
		$disk = intval($request->input('disk')) / 100;

		if(is_null($request->input('uptime'))){
			return Response::json([
				'status'  => 'fail',
				'code'    => 400,
				'data'    => '',
				'message' => '上报节点心跳信息失败，请检查字段'
			]);
		}

		$obj = new SsNodeInfo();
		$obj->node_id = $id;
		$obj->uptime = intval($request->input('uptime'));
		//$obj->load = $request->input('load');
		$obj->load = implode(' ', [$cpu, $mem, $disk]);
		$obj->log_time = time();
		$obj->save();

		if($obj->id){
			return Response::json([
				'status'  => 'success',
				'code'    => 200,
				'data'    => '',
				'message' => '上报节点心跳信息成功'
			]);
		}

		return Response::json([
			'status'  => 'fail',
			'code'    => 400,
			'data'    => '',
			'message' => '上报节点心跳信息失败，请检查字段'
		]);
	}

	// 上报节点在线人数
	public function setNodeOnline(Request $request, $id) {
		$inputArray = $request->all();
		$onlineCount = 0;
		foreach($inputArray as $input){
			if(!array_key_exists('ip', $input) || !array_key_exists('uid', $input)){
				return Response::json([
					'status'  => 'fail',
					'code'    => 400,
					'data'    => '',
					'message' => '上报节点在线情况失败，请检查字段'
				]);
			}elseif(!isset($input['ip']) || !isset($input['uid'])){
				return Response::json([
					'status'  => 'fail',
					'code'    => 400,
					'data'    => '',
					'message' => '上报节点在线情况失败，请检查字段'
				]);
			}

			$obj = new SsNodeIp();
			$obj->node_id = $id;
			$obj->user_id = $input['uid'];
			$obj->ip = $input['ip'];
			$obj->port = User::find($input['uid'])->port;
			$obj->created_at = time();
			$obj->save();

			if(!$obj->id){
				return Response::json([
					'status'  => 'fail',
					'code'    => 400,
					'data'    => '',
					'message' => '上报节点在线情况失败，请检查字段'
				]);
			}
			$onlineCount++;
		}

		$obj = new SsNodeOnlineLog();
		$obj->node_id = $id;
		$obj->online_user = $onlineCount;
		$obj->log_time = time();
		$obj->save();

		if($obj->id){
			return Response::json([
				'status'  => 'success',
				'code'    => 200,
				'data'    => '',
				'message' => '上报节点在线情况成功'
			]);
		}

		return Response::json([
			'status'  => 'fail',
			'code'    => 400,
			'data'    => '',
			'message' => '上报节点在线情况失败，请检查字段'
		]);
	}

	// 获取节点可用的用户列表
	public function getUserList(Request $request, $id) {
		$node = SsNode::query()->whereId($id)->first();
		$users = User::query()->where('status', '<>', -1)->whereEnable(1)->where('level', '>=', $node->level)->get();
		$data = [];
		foreach($users as $user){
			$new = [
				"uid"         => $user->id,
				"vmess_uid"   => $user->vmess_id,
				"speed_limit" => $user->speed_limit
			];
			array_push($data, $new);
		}

		if($data){
			return Response::json([
				'status'     => 'success',
				'code'       => 200,
				'data'       => $data,
				'message'    => '获取用户列表成功',
				'updateTime' => time()
			]);
		}

		return Response::json([
			'status'  => 'fail',
			'code'    => 400,
			'data'    => '',
			'message' => '获取用户列表失败'
		]);
	}

	// 上报用户流量日志
	public function setUserTraffic(Request $request, $id) {
		$inputArray = $request->all();

		foreach($inputArray as $input){
			if(!array_key_exists('uid', $input)){
				return Response::json([
					'status'  => 'fail',
					'code'    => 400,
					'data'    => '',
					'message' => '上报用户流量日志失败，请检查字段'
				]);
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
				return Response::json([
					'status'  => 'fail',
					'code'    => 400,
					'data'    => '',
					'message' => '上报用户流量日志失败，请检查字段'
				]);
			}
		}

		return Response::json([
			'status'  => 'success',
			'code'    => 200,
			'data'    => '',
			'message' => '上报用户流量日志成功'
		]);
	}

	// 获取节点的审计规则
	public function getNodeRule($id) {
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
						array_push($data, $new);
					}
				}

				return Response::json([
					'status'  => 'success',
					'code'    => 200,
					'data'    => [
						'mode'  => $ruleGroup->type? 'reject' : 'allow',
						'rules' => $data
					],
					'message' => '获取节点审计规则成功'
				]);

			}
		}

		return Response::json([
			//放行
			'status'  => 'success',
			'code'    => 200,
			'data'    => [
				'mode'  => 'all',
				'rules' => $data
			],
			'message' => '获取节点审计规则成功'
		]);
	}

	// todo: test required
	// 上报用户触发的审计规则记录
	public function addRuleLog(Request $request, $id) {
		if($request->has(['uid', 'rule_id', 'reason'])){
			$obj = new RuleLog();
			$obj->user_id = $request->input(['uid']);
			$obj->node_id = $id;
			$obj->rule_id = $request->input(['rule_id']);
			$obj->reason = $request->input(['reason']);
			$obj->save();

			if($obj->id){
				return Response::json([
					'status'  => 'success',
					'code'    => 200,
					'data'    => '',
					'message' => '上报用户触发审计规则记录成功'
				]);
			}
		}

		return Response::json([
			'status'  => 'fail',
			'code'    => 400,
			'data'    => '',
			'message' => '上报用户触发审计规则记录失败'
		]);

	}

	// 上报节点伪装域名证书信息
	public function addCertificate(Request $request, $id) {
		if($request->has(['key', 'pem'])){
			$node = SsNode::find($id);
			$obj = new NodeCertificate();
			$obj->domain = $node->server;
			$obj->key = $request->input(['key']);
			$obj->pem = $request->input(['pem']);
			$obj->save();

			if($obj->id){
				return Response::json([
					'status'  => 'success',
					'code'    => 200,
					'data'    => '',
					'message' => '上报节点伪装域名证书成功'
				]);
			}
		}
		return Response::json([
			'status'  => 'fail',
			'code'    => 400,
			'data'    => '',
			'message' => '上报节点伪装域名证书失败，请检查字段'
		]);
	}
}
