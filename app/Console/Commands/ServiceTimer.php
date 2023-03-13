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

    public function handle()
    {
        $jobTime = microtime(true);

        $this->decGoodsTraffic(); // 扣减用户到期商品的流量

        $jobTime = round(microtime(true) - $jobTime, 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobTime.'秒');
    }

    // 扣减用户到期商品的流量
    private function decGoodsTraffic()
    {
        //获取失效的套餐
        Order::activePlan()
            ->where('expired_at', '<=', date('Y-m-d H:i:s'))
            ->with('user')->whereHas('user') // 无用户订单，跳过
            ->chunk(config('tasks.chunk'), function ($orders) {
                foreach ($orders as $order) {
                    $user = $order->user;

                    $user->update([ // 清理全部流量,重置重置日期和等级
                        'u'               => 0,
                        'd'               => 0,
                        'transfer_enable' => 0,
                        'reset_time'      => null,
                        'level'           => 0,
                    ]);
                    Helpers::addUserTrafficModifyLog($user->id, $order->id, $user->transfer_enable, 0, '[定时任务]用户所购商品到期，扣减商品对应的流量');

                    sleep(1); // 保证一切都已经更新到位

                    $order->update(['is_expire' => 1]); // 过期本订单
                }
            });
    }
}
