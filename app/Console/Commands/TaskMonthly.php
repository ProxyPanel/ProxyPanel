<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Log;

class TaskMonthly extends Command
{
    protected $signature = 'task:monthly';

    protected $description = '每月任务';

    public function handle(): void
    {
        $jobTime = microtime(true);

        if (sysConfig('data_exhaust_notification')) {
            $this->cleanAccounts(); // 用户流量超过警告阈值提醒
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    private function cleanAccounts()
    {
        // 账号遗留结算的流量
        User::where('expired_at', '<', date('Y-m-d'))->where('transfer_enable', '==', 0)->whereEnable(0)
            ->whereRaw('u + d > transfer_enable')->update(['u' => 0, 'd' => 0]);
    }
}
