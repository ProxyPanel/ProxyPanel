<?php

namespace App\Http\Controllers\Api;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Models\SsNode;
use App\Models\User;
use App\Models\UserSubscribe;
use App\Models\UserSubscribeLog;
use Cache;
use DB;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

/**
 * 登录接口
 *
 * Class LoginController
 *
 * @package App\Http\Controllers
 */
class LoginController extends Controller {
	protected static $systemConfig;

	public function __construct() {
		self::$systemConfig = Helpers::systemConfig();
	}

	// 登录返回订阅信息
	public function login(Request $request): ?JsonResponse {
		$email = $request->input('email');
		$password = $request->input('password');
		$cacheKey = 'request_times_'.md5(getClientIp());

		if(!$email || !$password){
			Cache::increment($cacheKey);

			return Response::json(['status' => 'fail', 'data' => [], 'message' => '请输入用户名和密码']);
		}

		// 连续请求失败15次，则封IP一小时
		if(Cache::has($cacheKey)){
			if(Cache::get($cacheKey) >= 15){
				return Response::json(['status' => 'fail', 'data' => [], 'message' => '请求失败超限，禁止访问1小时']);
			}
		}else{
			Cache::put($cacheKey, 1, Hour);
		}

		$user = User::query()->whereEmail($email)->where('status', '>=', 0)->first();
		if(!$user){
			Cache::increment($cacheKey);

			return Response::json(['status' => 'fail', 'data' => [], 'message' => '账号不存在或已被禁用']);
		}

		if(!Hash::check($password, $user->password)){
			return Response::json(['status' => 'fail', 'data' => [], 'message' => '用户名或密码错误']);
		}

		try{
			DB::beginTransaction();
			// 如果未生成过订阅链接则生成一个
			$subscribe = UserSubscribe::query()->whereUserId($user->id)->first();

			// 更新订阅链接访问次数
			$subscribe->increment('times', 1);

			// 记录每次请求
			$this->subscribeLog($subscribe->id, getClientIp(), 'API访问');

			// 订阅链接
			$url = self::$systemConfig['subscribe_domain']?: self::$systemConfig['website_url'];

			// 节点列表
			$nodeList = SsNode::query()->whereStatus(1)->where('level', '<=', $user->level)->get();


			$c_nodes = collect();
			foreach($nodeList as $node){
				$temp_node = [
					'name'          => $node->name,
					'server'        => $node->server,
					'server_port'   => $user->port,
					'method'        => $user->method,
					'obfs'          => $user->obfs,
					'flags'         => $url.'/assets/images/country/'.$node->country_code.'.png',
					'obfsparam'     => '',
					'password'      => $user->passwd,
					'group'         => '',
					'protocol'      => $user->protocol,
					'protoparam'    => '',
					'protocolparam' => ''
				];
				$c_nodes = $c_nodes->push($temp_node);
			}

			$data = [
				'status'       => 1,
				'class'        => 0,
				'level'        => 2,
				'expire_in'    => $user->expire_time,
				'text'         => '',
				'buy_link'     => '',
				'money'        => '0.00',
				'sspannelName' => 'proxypanel',
				'usedTraffic'  => flowAutoShow($user->u + $user->d),
				'Traffic'      => flowAutoShow($user->transfer_enable),
				'all'          => 1,
				'residue'      => '',
				'nodes'        => $c_nodes,
				'link'         => $url.'/s/'.$subscribe->code
			];

			DB::commit();

			return Response::json(['status' => 'success', 'data' => $data, 'message' => '登录成功']);
		}catch(Exception $e){
			DB::rollBack();

			return Response::json(['status' => 'success', 'data' => [], 'message' => '登录失败']);
		}
	}

	// 写入订阅访问日志
	private function subscribeLog($subscribeId, $ip, $headers): void {
		$log = new UserSubscribeLog();
		$log->sid = $subscribeId;
		$log->request_ip = $ip;
		$log->request_time = date('Y-m-d H:i:s');
		$log->request_header = $headers;
		$log->save();
	}
}
