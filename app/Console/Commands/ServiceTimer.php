<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Http\Controllers\ServiceController;
use App\Http\Models\Order;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use DB;
use Exception;
use Illuminate\Console\Command;
use Log;

class ServiceTimer extends Command
{
	protected $signature = 'serviceTimer';
	protected $description = '服务计时器';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$jobStartTime = microtime(TRUE);

		// 扣减用户到期商品的流量
		$this->decGoodsTraffic();

		$jobEndTime = microtime(TRUE);
		$jobUsedTime = round(($jobEndTime-$jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	// 扣减用户到期商品的流量
	private function decGoodsTraffic()
	{
		//获取失效的套餐
		$orderList = Order::query()->with(['goods'])->where('status', 2)->where('is_expire', 0)->whereHas('goods', function($q){ $q->where('type', 2); })->where('expire_at', '<=', date('Y-m-d H:i:s'))->get();
		if($orderList->isNotEmpty()){
			try{
				DB::beginTransaction();
				foreach($orderList as $order){
					// 过期本订单
					Order::query()->where('oid', $order->oid)->update(['is_expire' => 1]);

					// 过期生效中的加油包
					Order::query()
						->with(['goods'])
						->where('user_id', $order->user_id)
						->where('status', 2)
						->where('is_expire', 0)
						->whereHas('goods', function($q){
							$q->where('type', 1);
						})->update(['is_expire' => 1]);

					if(empty($order->user) || empty($order->goods)){
						continue;
					}

					// 清理全部流量
					Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $order->user->transfer_enable, 0, '[定时任务]用户所购商品到期，扣减商品对应的流量');
					User::query()->where('id', $order->user_id)->update(['u' => 0, 'd' => 0, 'transfer_enable' => 0, 'reset_time' => NULL]);

					// 删除对应用户的所有标签
					UserLabel::query()->where('user_id', $order->user_id)->delete();

					// 检查该订单对应用户是否有预支付套餐
					$prepaidOrder = Order::query()->where('user_id', $order->user_id)->where('status', 3)->orderBy('oid', 'asc')->first();

					if($prepaidOrder){
						(new ServiceController)->activePrepaidOrder($prepaidOrder->oid);
					}
				}

				DB::commit();
			} catch(Exception $e){
				Log::error($this->description.'：'.$e);

				DB::rollBack();
			}
		}
	}
}
