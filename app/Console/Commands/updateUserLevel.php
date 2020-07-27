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
		foreach(User::query()->where('level', '<>', 0)->get() as $user){
			User::query()->whereId($user->id)->update(['level' => 0]);
		}
		// 获取商品列表，取新等级
		$goodList = Goods::query()->where('level', '<>', 0)->whereType(2)->get();
		// 取生效的套餐
		$orderList = Order::query()
		                  ->whereIn('goods_id', $goodList->pluck('id')->toArray())
		                  ->whereStatus(2)
		                  ->whereIsExpire(0)
		                  ->get();
		foreach($orderList as $order){
			$ret = User::query()->whereId($order->user_id)->update(['level' => $order->goods->level]);

			if($ret){
				Log::info('用户： '.$order->user->id.', 按照订单'.$order->id.' 等级为'.$order->goods->level);
			}else{
				Log::error('用户： '.$order->user->id.' 等级更新失败！');
			}
		}
		Log::info('----------------------------【用户等级升级】结束----------------------------');
	}
}