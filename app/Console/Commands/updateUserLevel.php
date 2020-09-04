<?php

namespace App\Console\Commands;

use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Log;

class updateUserLevel extends Command {
	protected $signature = 'updateUserLevel';
	protected $description = '更新用户等级';

	public function handle(): void {
		Log::info('----------------------------【用户等级升级】开始----------------------------');
		// 预设level 0
		User::where('level', '<>', 0)->update(['level' => 0]);

		// 获取商品列表，取新等级
		$goodsLevel = Goods::type(2)->where('level', '<>', 0)->pluck('id')->toArray();
		// 取生效的套餐
		$orderList = Order::active()->with('goods')->whereIn('goods_id', $goodsLevel)->get();
		foreach($orderList as $order){
			$ret = $order->user->update(['level' => $order->goods->level]);

			if($ret){
				Log::info('用户： '.$order->user_id.', 按照订单'.$order->id.' 等级为'.$order->goods->level);
			}else{
				Log::error('用户： '.$order->user_id.' 等级更新失败！');
			}
		}
		Log::info('----------------------------【用户等级升级】结束----------------------------');
	}
}