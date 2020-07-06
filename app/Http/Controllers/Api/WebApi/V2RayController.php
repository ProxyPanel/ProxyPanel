<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Components\Helpers;
use App\Models\NodeCertificate;
use App\Models\SsNode;
use App\Models\User;
use Illuminate\Http\Request;

class V2RayController extends BaseController {
	// 获取节点信息
	public function getNodeInfo($id) {
		$node = SsNode::query()->whereId($id)->first();
		$nodeTls = NodeCertificate::query()->whereId($node->server)->first();

		return $this->returnData('获取节点信息成功', 'success', 200, [
			'id'              => $node->id,
			'is_udp'          => $node->is_udp? true : false,
			'speed_limit'     => $node->speed_limit,
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
			'v2_tls'          => $node->v2_tls? true : false,
			'v2_tls_provider' => $node->tls_provider,
		]);
	}

	// 获取节点可用的用户列表
	public function getUserList(Request $request, $id) {
		$node = SsNode::query()->whereId($id)->first();
		$users = User::query()->where('status', '<>', -1)->whereEnable(1)->where('level', '>=', $node->level)->get();
		$data = [];

		foreach($users as $user){
			$new = [
				'uid'         => $user->id,
				'vmess_uid'   => $user->vmess_id,
				'speed_limit' => $user->speed_limit
			];
			array_push($data, $new);
		}

		if($data){
			return $this->returnData('获取用户列表成功', 'success', 200, $data, ['updateTime' => time()]);
		}

		return $this->returnData('获取用户列表失败');
	}

	// 上报节点伪装域名证书信息
	public function addCertificate(Request $request, $id) {
		if($request->has(['key', 'pem'])){
			$node = SsNode::find($id);
			$obj = new NodeCertificate();
			$obj->domain = $node->server;
			$obj->key = $request->input('key');
			$obj->pem = $request->input('pem');
			$obj->save();

			if($obj->id){
				return $this->returnData('上报节点伪装域名证书成功', 'success', 200);
			}
		}

		return $this->returnData('上报节点伪装域名证书失败，请检查字段');
	}
}
