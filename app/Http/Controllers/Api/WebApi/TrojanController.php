<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Components\Helpers;
use App\Models\SsNode;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class TrojanController extends BaseController {
	// 获取节点信息
	public function getNodeInfo($id): JsonResponse {
		$node = SsNode::query()->whereId($id)->first();

		return $this->returnData('获取节点信息成功', 'success', 200, [
			'id'           => $node->id,
			'is_udp'       => $node->is_udp? true : false,
			'speed_limit'  => $node->speed_limit,
			'client_limit' => $node->client_limit,
			'push_port'    => $node->push_port,
			'redirect_url' => Helpers::systemConfig()['redirect_url'],
			'trojan_port'  => $node->port,
			'secret'       => $node->auth->secret,
			'license'      => Helpers::systemConfig()['trojan_license'],
		]);
	}

	// 获取节点可用的用户列表
	public function getUserList($id): JsonResponse {
		$node = SsNode::query()->whereId($id)->first();
		$users = User::query()->where('status', '<>', -1)->whereEnable(1)->where('level', '>=', $node->level)->get();
		$data = [];

		foreach($users as $user){
			$new = [
				'uid'         => $user->id,
				'password'    => $user->passwd,
				'speed_limit' => $user->speed_limit
			];
			$data[] = $new;
		}

		return $this->returnData('获取用户列表成功', 'success', 200, $data, ['updateTime' => time()]);
	}
}
