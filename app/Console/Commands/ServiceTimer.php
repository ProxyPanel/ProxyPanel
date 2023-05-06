<?php

namespace App\Console\Commands;

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

        $this->expiredPlan(); // 过期套餐

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    private function expiredPlan()
    {
        Order::activePlan()
            ->where('expired_at', '<=', date('Y-m-d H:i:s'))
            ->chunk(config('tasks.chunk'), function ($orders) {
                $orders->each->expired(); // 过期订单
            });
    }
}
