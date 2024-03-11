<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserBanedLog;
use App\Models\UserLoginLog;
use Exception;
use Illuminate\Console\Command;
use Log;

class TaskMonthly extends Command
{
    protected $signature = 'task:monthly';

    protected $description = '每月任务';

    public function handle(): void
    {
        $jobTime = microtime(true);

        $this->cleanAccounts(); // 清理僵尸账号

        if (sysConfig('is_clear_log')) {
            $this->clearLog(); // 清除小日志
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    private function cleanAccounts(): void
    {
        User::where('expired_at', '<', date('Y-m-d'))->where('transfer_enable', '=', 0)->whereEnable(0)->where(function ($query) {
            $query->where('u', '>', 0)->orWhere('d', '>', 0);
        })->update(['u' => 0, 'd' => 0]);
    }

    private function clearLog(): void
    {
        try {
            NotificationLog::where('updated_at', '<=', date('Y-m-d H:i:s', strtotime(config('tasks.clean.notification_logs'))))->delete(); // 清理通知日志

            UserLoginLog::where('created_at', '<=', date('Y-m-d H:i:s', strtotime(config('tasks.clean.login_logs'))))->delete(); // 清除用户登陆日志

            Payment::where('created_at', '<=', date('Y-m-d H:i:s', strtotime(config('tasks.clean.payments'))))->delete(); // 清理在线支付日志

            UserBanedLog::where('created_at', '<=', date('Y-m-d H:i:s', strtotime(config('tasks.clean.user_baned_logs'))))->delete(); // 清理用户封禁日志

            Order::whereStatus(-1)->where('created_at', '<=', date('Y-m-d H:i:s', strtotime(config('tasks.clean.unpaid_orders'))))->delete(); // 清理用户未支付订单
        } catch (Exception $e) {
            Log::emergency('【清理日志】错误： '.$e->getMessage());
        }
    }
}
