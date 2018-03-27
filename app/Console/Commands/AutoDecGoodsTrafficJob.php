<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Order;
use App\Http\Models\User;
use Log;

class AutoDecGoodsTrafficJob extends Command
{
    protected $signature = 'autoDecGoodsTrafficJob';
    protected $description = '自动扣除用户到期流量包的流量';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $orderList = Order::query()->with(['user', 'goods'])->where('is_expire', 0)->get();
        if (!$orderList->isEmpty()) {
            foreach ($orderList as $order) {
                if (empty($order->user) || empty($order->goods)) {
                    continue;
                }

                // 到期自动处理
                if (date("Y-m-d H:i:s") >= $order->expire_at) {
                    if ($order->user->transfer_enable - $order->goods->traffic * 1048576 <= 0) {
                        User::query()->where('id', $order->user_id)->update(['transfer_enable' => 0]);
                    } else {
                        User::query()->where('id', $order->user_id)->decrement('transfer_enable', $order->goods->traffic * 1048576);
                    }

                    Order::query()->where('oid', $order->oid)->update(['is_expire' => 1]);
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
