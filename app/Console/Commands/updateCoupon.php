<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Command;
use Log;

class updateCoupon extends Command {
	protected $signature = 'updateCoupon';
	protected $description = '修改原版Coupon至新版';


	public function __construct() {
		parent::__construct();
	}

	public function handle() {
		Log::info('----------------------------【优惠券转换】开始----------------------------');
		$coupons = Coupon::withTrashed()->get();
		foreach($coupons as $coupon){
			if($coupon->amount){
				$coupon->value = $coupon->amount / 100;
			}elseif($coupon->discount){
				$coupon->value = $coupon->discount * 100;
			}

			if($coupon->rule === 0){
				$coupon->rule = null;
			}else{
				$coupon->rule /= 100;
			}
			$coupon->save();
		}
		Log::info('----------------------------【优惠券转换】结束----------------------------');
	}
}
