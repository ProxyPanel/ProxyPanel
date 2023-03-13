<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Log;

class UpdateUserSpeedLimit extends Command
{
    protected $signature = 'updateUserSpeedLimit';
    protected $description = '根据商品更新用户限速';

    public function handle()
    {
        $jobTime = microtime(true);

        foreach (Order::whereStatus(2)->whereIsExpire(0)->where('goods_id', '<>', null)->oldest()->with(['user', 'goods'])->has('goods')->has('user')->get() as $order) {
            $order->user->update(['speed_limit' => $order->goods->speed_limit]);
        }

        $jobTime = round(microtime(true) - $jobTime, 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobTime.'秒');
    }
}
