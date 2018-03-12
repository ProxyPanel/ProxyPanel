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
        $order = Order::query()->with(['user', 'goods'])->where('is_expire', 0)->get();
        if (!$order->isEmpty()) {
            foreach ($order as $vo) {
                if (empty($vo->user) || empty($vo->goods)) {
                    continue;
                }

                // 到期自动处理
                if (date("Y-m-d H:i:s") >= $vo->expire_at) {
                    if ($vo->user->transfer_enable - $vo->goods->traffic * 1048576 <= 0) {
                        User::query()->where('id', $vo->user_id)->update(['transfer_enable' => 0]);
                    } else {
                        User::query()->where('id', $vo->user_id)->decrement('transfer_enable', $vo->goods->traffic * 1048576);
                    }

                    Order::query()->where('oid', $vo->oid)->update(['is_expire' => 1]);
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
