<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Coupon;
use Log;

class AutoExpireCouponJob extends Command
{
    protected $signature = 'autoExpireCouponJob';
    protected $description = '优惠券到期自动置无效';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $couponList = Coupon::query()->where('status', 0)->where('available_end', '<=', time())->get();
        if (!$couponList->isEmpty()) {
            foreach ($couponList as $coupon) {
                Coupon::query()->where('id', $coupon->id)->update(['status' => 2]);
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
