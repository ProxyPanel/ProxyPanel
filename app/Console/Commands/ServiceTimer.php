<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Models\Order;
use Illuminate\Console\Command;
use Log;

class ServiceTimer extends Command
{
    protected $signature = 'serviceTimer';
    protected $description = '服务计时器';

    public function handle(): void
    {
        $jobStartTime = microtime(true);

        // 扣减用户到期商品的流量
        $this->decGoodsTraffic();

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    // 扣减用户到期商品的流量
    private function decGoodsTraffic(): void
    {
        //获取失效的套餐
        foreach (Order::activePlan()->where('expired_at', '<=', date('Y-m-d H:i:s'))->with('user')->get() as $order) {
            // 清理全部流量,重置重置日期和等级 TODO 可用流量变动日志加入至UserObserver
            $user = $order->user;
            // 无用户订单，跳过
            if (! $user) {
                continue;
            }
            $user->update([
                'u'               => 0,
                'd'               => 0,
                'transfer_enable' => 0,
                'reset_time'      => null,
                'level'           => 0,
            ]);
            Helpers::addUserTrafficModifyLog($user->id, $order->id, $user->transfer_enable, 0, '[定时任务]用户所购商品到期，扣减商品对应的流量');

            // 过期本订单
            $order->update(['is_expire' => 1]);
        }
    }
}
