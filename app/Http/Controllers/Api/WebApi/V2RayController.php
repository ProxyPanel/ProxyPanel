<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Components\Helpers;
use App\Models\NodeCertificate;
use App\Models\SsNode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class V2RayController extends BaseController {
	// 获取节点信息
	public function getNodeInfo($id): JsonResponse {
		$node = SsNode::query()->whereId($id)->first();
		$nodeDv = NodeCertificate::query()->whereId($node->server)->first();

		return $this->returnData('获取节点信息成功', 'success', 200, [
			'id'              => $node->id,
			'is_udp'          => $node->is_udp? true : false,
			'speed_limit'     => $node->speed_limit,
			'client_limit'    => $node->client_limit,
			'push_port'       => $node->push_port,
			'redirect_url'    => Helpers::systemConfig()['redirect_url'],
			'secret'          => $node->auth->secret,
			'key'             => $nodeDv? $nodeDv->key : '',
			'pem'             => $nodeDv? $nodeDv->pem : '',
			'v2_license'      => Helpers::systemConfig()['v2ray_license'],
			'v2_alter_id'     => $node->v2_alter_id,
			'v2_port'         => $node->v2_port,
			'v2_method'       => $node->v2_method,
			'v2_net'          => $node->v2_net,
			'v2_type'         => $node->v2_type,
			'v2_host'         => $node->v2_host,
			'v2_path'         => $node->v2_path,
			'v2_tls'          => $node->v2_tls? true : false,
			'v2_tls_provider' => $node->tls_provider?: Helpers::systemConfig()['v2ray_tls_provider'],
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
				'vmess_uid'   => $user->vmess_id,
				'speed_limit' => $user->speed_limit
			];
			$data[] = $new;
		}

		return $this->returnData('获取用户列表成功', 'success', 200, $data, ['updateTime' => time()]);
	}

	// 上报节点伪装域名证书信息
	public function addCertificate(Request $request, $id): JsonResponse {
		$key = $request->input('key');
		$pem = $request->input('pem');

		if($request->has(['key', 'pem'])){
			$node = SsNode::find($id);
			$Dv = NodeCertificate::query()->whereDomain($node->v2_host)->first();
			if($Dv){
				$ret = NodeCertificate::query()->whereId($Dv->id)->update(['key' => $key, 'pem' => $pem]);
			}else{
				$ret = new NodeCertificate();
				$ret->domain = $node->server;
				$ret->key = $request->input('key');
				$ret->pem = $request->input('pem');
				$ret->save();
			}

			if($ret){
				return $this->returnData('上报节点伪装域名证书成功', 'success', 200);
			}
		}

		return $this->returnData('上报节点伪装域名证书失败，请检查字段');
	}
}
