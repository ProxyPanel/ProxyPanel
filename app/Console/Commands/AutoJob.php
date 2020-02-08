<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\ServerChan;
use App\Http\Models\Coupon;
use App\Http\Models\Invite;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\User;
use App\Http\Models\UserBanLog;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use App\Http\Models\UserTrafficHourly;
use App\Http\Models\VerifyCode;
use DB;
use Exception;
use Illuminate\Console\Command;
use Log;

class AutoJob extends Command
{
	protected static $systemConfig;
	protected $signature = 'autoJob';
	protected $description = '自动化任务';

	public function __construct()
	{
		parent::__construct();
		self::$systemConfig = Helpers::systemConfig();
	}

	/*
	 * 警告：除非熟悉业务流程，否则不推荐更改以下执行顺序，随意变更以下顺序可能导致系统异常
	 */
	public function handle()
	{
		$jobStartTime = microtime(TRUE);

		// 关闭超时未支付订单
		$this->closeOrders();

		//过期验证码、优惠券、邀请码无效化
		$this->expireCode();

		// 封禁访问异常的订阅链接
		$this->blockSubscribe();

		// 封禁账号
		$this->blockUsers();

		// 解封被封禁的账号
		$this->unblockUsers();

		// 端口回收与分配
		$this->dispatchPort();

		// 检测节点是否离线
		$this->checkNodeStatus();

		$jobEndTime = microtime(TRUE);
		$jobUsedTime = round(($jobEndTime-$jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	// 关闭超时未支付订单
	private function closeOrders()
	{
		// 关闭超时未支付的在线支付订单（在线支付收款二维码超过30分钟自动关闭，关闭后无法再支付，所以我们限制15分钟内必须付款）
		$paymentList = Payment::query()->where('status', 0)->where('created_at', '<=', date("Y-m-d H:i:s", strtotime("-15 minutes")))->get();
		if($paymentList->isNotEmpty()){
			DB::beginTransaction();
			try{
				foreach($paymentList as $payment){
					// 关闭支付单
					Payment::query()->where('id', $payment->id)->update(['status' => -1]);

					// 关闭订单
					Order::query()->where('oid', $payment->oid)->update(['status' => -1]);

					// 退回优惠券
					if($payment->order->coupon_id){
						Coupon::query()->where('id', $payment->order->coupon_id)->update(['status' => 0]);

						Helpers::addCouponLog($payment->order->coupon_id, $payment->order->goods_id, $payment->oid, '订单超时未支付，自动退回');
					}
				}

				DB::commit();
			} catch(Exception $e){
				Log::info('【异常】自动关闭超时未支付订单：'.$e);

				DB::rollBack();
			}
		}
	}

	// 注册验证码自动置无效
	private function expireCode()
	{
		// 注册验证码自动置无效
		VerifyCode::query()->where('status', 0)->where('created_at', '<=', date('Y-m-d H:i:s', strtotime("-10 minutes")))->update(['status' => 2]);

		// 优惠券到期自动置无效
		Coupon::query()->where('status', 0)->where('available_end', '<=', time())->update(['status' => 2]);

		// 邀请码到期自动置无效
		Invite::query()->where('status', 0)->where('dateline', '<=', date('Y-m-d H:i:s'))->update(['status' => 2]);
	}

	// 封禁访问异常的订阅链接
	private function blockSubscribe()
	{
		if(self::$systemConfig['is_subscribe_ban']){
			$userList = User::query()->where('status', '>=', 0)->where('enable', 1)->get();
			foreach($userList as $user){
				$subscribe = UserSubscribe::query()->where('user_id', $user->id)->first();
				if($subscribe){
					// 24小时内不同IP的请求次数
					$request_times = UserSubscribeLog::query()->where('sid', $subscribe->id)->where('request_time', '>=', date("Y-m-d H:i:s", strtotime("-24 hours")))->distinct('request_ip')->count('request_ip');
					if($request_times >= self::$systemConfig['subscribe_ban_times']){
						UserSubscribe::query()->where('id', $subscribe->id)->update(['status' => 0, 'ban_time' => time(), 'ban_desc' => '存在异常，自动封禁']);

						// 记录封禁日志
						$this->addUserBanLog($subscribe->user_id, 0, '【完全封禁订阅】-订阅24小时内请求异常');
					}
				}
			}
		}
	}

	/**
	 * 添加用户封禁日志
	 *
	 * @param int    $userId  用户ID
	 * @param int    $minutes 封禁时长，单位分钟
	 * @param string $desc    封禁理由
	 */
	private function addUserBanLog($userId, $minutes, $desc)
	{
		$log = new UserBanLog();
		$log->user_id = $userId;
		$log->minutes = $minutes;
		$log->desc = $desc;
		$log->save();
	}

	// 封禁账号
	private function blockUsers()
	{
		// 封禁1小时内流量异常账号
		if(self::$systemConfig['is_traffic_ban']){
			$userList = User::query()->where('enable', 1)->where('status', '>=', 0)->where('ban_time', 0)->get();
			foreach($userList as $user){
				// 对管理员豁免
				if($user->is_admin){
					continue;
				}

				// 多往前取5分钟，防止数据统计任务执行时间过长导致没有数据
				$totalTraffic = UserTrafficHourly::query()->where('user_id', $user->id)->where('node_id', 0)->where('created_at', '>=', date('Y-m-d H:i:s', time()-3900))->sum('total');
				if($totalTraffic >= (self::$systemConfig['traffic_ban_value']*1073741824)){
					User::query()->where('id', $user->id)->update(['enable' => 0, 'ban_time' => strtotime(date('Y-m-d H:i:s', strtotime("+".self::$systemConfig['traffic_ban_time']." minutes")))]);

					// 写入日志
					$this->addUserBanLog($user->id, self::$systemConfig['traffic_ban_time'], '【临时封禁代理】-1小时内流量异常');
				}
			}
		}

		// 禁用流量超限用户
		$userList = User::query()->where('enable', 1)->where('status', '>=', 0)->where('ban_time', 0)->whereRaw("u + d >= transfer_enable")->get();
		foreach($userList as $user){
			User::query()->where('id', $user->id)->update(['enable' => 0]);

			// 写入日志
			$this->addUserBanLog($user->id, 0, '【封禁代理】-流量已用完');
		}
	}

	// 解封被临时封禁的账号
	private function unblockUsers()
	{
		// 解封被临时封禁的账号
		$userList = User::query()->where('enable', 0)->where('status', '>=', 0)->where('ban_time', '>', 0)->get();
		foreach($userList as $user){
			if($user->ban_time < time()){
				User::query()->where('id', $user->id)->update(['enable' => 1, 'ban_time' => 0]);

				// 写入操作日志
				$this->addUserBanLog($user->id, 0, '【自动解封】-临时封禁到期');
			}
		}

		// 可用流量大于已用流量也解封（比如：邀请返利自动加了流量）
		$userList = User::query()->where('enable', 0)->where('status', '>=', 0)->where('ban_time', 0)->where('expire_time', '>=', date('Y-m-d'))->whereRaw("u + d < transfer_enable")->get();
		foreach($userList as $user){
			User::query()->where('id', $user->id)->update(['enable' => 1]);

			// 写入操作日志
			$this->addUserBanLog($user->id, 0, '【自动解封】-有流量解封');
		}
	}

	// 端口回收与分配
	private function dispatchPort()
	{
		if(self::$systemConfig['auto_release_port']){
			## 自动分配端口
			$userList = User::query()->where('enable', 1)->where('status', '>=', 0)->where('port', 0)->get();
			foreach($userList as $user){
				$port = self::$systemConfig['is_rand_port']? Helpers::getRandPort() : Helpers::getOnlyPort();

				User::query()->where('id', $user->id)->update(['port' => $port]);
			}

			## 被封禁的账号自动释放端口
			User::query()->where('enable', 0)->where('status', -1)->where('port', '!=', 0)->update(['port' => 0]);

			## 过期一个月的账户自动释放端口
			User::query()->where('enable', 0)->where('port', '!=', 0)->where('expire_time', '<=', date("Y-m-d", strtotime("-30 days")))->update(['port' => 0]);
		}
	}

	// 检测节点是否离线
	private function checkNodeStatus()
	{
		if(Helpers::systemConfig()['is_node_crash_warning']){
			$nodeList = SsNode::query()->where('is_transit', 0)->where('status', 1)->get();
			foreach($nodeList as $node){
				// 10分钟内无节点负载信息则认为是后端炸了
				$nodeTTL = SsNodeInfo::query()->where('node_id', $node->id)->where('log_time', '>=', strtotime("-10 minutes"))->orderBy('id', 'desc')->doesntExist();
				if($nodeTTL){
					ServerChan::send('节点异常警告', "节点**{$node->name}【{$node->ip}】**异常：**心跳异常，可能离线了**");
				}
			}
		}
	}
}
