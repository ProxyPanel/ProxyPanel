<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Models\Node;
use Illuminate\Http\JsonResponse;

class TrojanController extends BaseController {
	// 获取节点信息
	public function getNodeInfo($id): JsonResponse {
		$node = Node::find($id);

		return $this->returnData('获取节点信息成功', 'success', 200, [
			'id'           => $node->id,
			'is_udp'       => $node->is_udp? true : false,
			'speed_limit'  => $node->speed_limit,
			'client_limit' => $node->client_limit,
			'push_port'    => $node->push_port,
			'redirect_url' => sysConfig('redirect_url'),
			'trojan_port'  => $node->port,
			'secret'       => $node->auth->secret,
			'license'      => sysConfig('trojan_license'),
		]);
	}

	// 获取节点可用的用户列表
	public function getUserList($id): JsonResponse {
		$users = Node::find($id)->node_access_users;
		$data = [];

		foreach($users as $user){
			$data[] = [
				'uid'         => $user->id,
				'password'    => $user->passwd,
				'speed_limit' => $user->speed_limit
			];
		}

		return $this->returnData('获取用户列表成功', 'success', 200, $data, ['updateTime' => time()]);
	}
}
