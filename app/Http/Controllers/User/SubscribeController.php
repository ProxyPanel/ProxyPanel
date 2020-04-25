<?php

namespace App\Http\Controllers\User;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Models\SsNode;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\UserSubscribe;
use Illuminate\Http\Request;
use Redirect;
use Response;

class SubscribeController extends Controller
{
	protected static $systemConfig;

	function __construct()
	{
		self::$systemConfig = Helpers::systemConfig();
	}

	// 通过订阅码获取订阅信息
	public function getSubscribeByCode(Request $request, $code)
	{
		if(empty($code)){
			return Redirect::to('login');
		}

		// 校验合法性
		$subscribe = UserSubscribe::query()->with('user')->whereStatus(1)->whereCode($code)->first();
		if(!$subscribe){
			exit($this->noneNode());
		}

		$user = User::query()->whereIn('status', [0, 1])->whereEnable(1)->whereId($subscribe->user_id)->first();
		if(!$user){
			exit($this->noneNode());
		}

		// 更新访问次数
		$subscribe->increment('times', 1);

		// 记录每次请求
		$this->log($subscribe->id, getClientIp(), $request->headers);

		// 获取这个账号可用节点
		$userLabelIds = UserLabel::query()->whereUserId($user->id)->pluck('label_id');
		if(empty($userLabelIds)){
			exit($this->noneNode());
		}

		$query = SsNode::query()->selectRaw('ss_node.*')->leftjoin("ss_node_label", "ss_node.id", "=", "ss_node_label.node_id");

		// 启用混合订阅时，加入V2Ray节点，未启用时仅下发SSR节点信息
		if(!self::$systemConfig['mix_subscribe']){
			$query->where('ss_node.type', 1);
		}

		$nodeList = $query->where('ss_node.status', 1)->where('ss_node.is_subscribe', 1)->whereIn('ss_node_label.label_id', $userLabelIds)->groupBy('ss_node.id')->orderBy('ss_node.sort', 'desc')->orderBy('ss_node.id', 'asc')->get()->toArray();
		if(empty($nodeList)){
			exit($this->noneNode());
		}

		// 打乱数组
		if(self::$systemConfig['rand_subscribe']){
			shuffle($nodeList);
		}

		$scheme = NULL;

		// 展示到期时间和剩余流量
		if(self::$systemConfig['is_custom_subscribe']){
			$scheme .= $this->expireDate($user).$this->lastTraffic($user);
		}

		// 控制客户端最多获取节点数
		foreach($nodeList as $key => $node){
			// 控制显示的节点数
			if(self::$systemConfig['subscribe_max'] && $key >= self::$systemConfig['subscribe_max']){
				break;
			}
			$scheme .= $this->getNodeInfo($user->id, $node['id'], 0).PHP_EOL;
		}

		// 适配Quantumult的自定义订阅头
		if(self::$systemConfig['is_custom_subscribe']){
			$headers = [
				'Content-type'          => 'application/octet-stream; charset=utf-8',
				'Cache-Control'         => 'no-store, no-cache, must-revalidate',
				'Subscription-Userinfo' => 'upload='.$user->u.'; download='.$user->d.'; total='.$user->transfer_enable.'; expire='.strtotime($user->expire_time)
			];

			return Response::make(base64url_encode($scheme), 200, $headers);
		}else{
			return Response::make(base64url_encode($scheme));
		}
	}

	// 抛出无可用的节点信息，用于兼容防止客户端订阅失败
	private function noneNode()
	{
		return base64url_encode('ssr://'.base64url_encode('0.0.0.0:1:origin:none:plain:'.base64url_encode('0000').'/?obfsparam=&protoparam=&remarks='.base64url_encode('无可用节点或账号被封禁或订阅被封禁').'&group='.base64url_encode('错误').'&udpport=0&uot=0')."\n");
	}

	/**
	 * 过期时间
	 *
	 * @param object $user
	 *
	 * @return string
	 */
	private function expireDate($user)
	{
		$text = '到期时间: '.$user->expire_time;

		return 'ssr://'.base64url_encode('0.0.0.1:1:origin:none:plain:'.base64url_encode('0000').'/?obfsparam=&protoparam=&remarks='.base64url_encode($text).'&group='.base64url_encode(self::$systemConfig['website_name']).'&udpport=0&uot=0')."\n";
	}

	/**
	 * 剩余流量
	 *
	 * @param object $user
	 *
	 * @return string
	 */
	private function lastTraffic($user)
	{
		$text = '剩余流量: '.flowAutoShow($user->transfer_enable-$user->u-$user->d);

		return 'ssr://'.base64url_encode('0.0.0.2:1:origin:none:plain:'.base64url_encode('0000').'/?obfsparam=&protoparam=&remarks='.base64url_encode($text).'&group='.base64url_encode(self::$systemConfig['website_name']).'&udpport=0&uot=0')."\n";
	}
}
