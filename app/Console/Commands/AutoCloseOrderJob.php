<?php

namespace App\Console\Commands;

use App\Http\Models\Order;
use App\Http\Models\Payment;
use Illuminate\Console\Command;
use Log;
use DB;

class AutoCloseOrderJob extends Command
{
    protected $signature = 'autoCloseOrderJob';
    protected $description = '自动关闭超时未支付订单';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 有赞云超过60分钟未支付则自动关闭，我们这则只给30分钟
        $paymentList = Payment::query()->where('status', 0)->where('created_at', '<=', date("Y-m-d H:i:s", strtotime("-30 minutes")))->get();
        if (!$paymentList->isEmpty()) {
            DB::beginTransaction();
            try {
                foreach ($paymentList as $payment) {
                    Payment::query()->where('id', $payment->id)->update(['status' => -1]); // 关闭支付单
                    Order::query()->where('oid', $payment->oid)->update(['status' => -1]); // 关闭订单
                }

                DB::commit();
            } catch (\Exception $e) {
                Log::info('【异常】自动关闭超时未支付订单：' . $e->getMessage());

                DB::rollBack();
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
