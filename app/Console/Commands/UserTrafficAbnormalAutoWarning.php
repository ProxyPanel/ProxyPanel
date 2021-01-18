<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserHourlyDataFlow;
use App\Notifications\DataAnomaly;
use Illuminate\Console\Command;
use Log;
use Notification;

class UserTrafficAbnormalAutoWarning extends Command
{
    protected $signature = 'userTrafficAbnormalAutoWarning';
    protected $description = '用户流量异常警告';

    public function handle(): void
    {
        $jobStartTime = microtime(true);

        // 用户流量异常警告
        if (sysConfig('data_anomaly_notification')) {
            $this->userTrafficAbnormalWarning();
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    // 用户流量异常警告
    private function userTrafficAbnormalWarning(): void
    {
        // 1小时内流量异常用户(多往前取5分钟，防止数据统计任务执行时间过长导致没有数据)
        $userTotalTrafficLogs = UserHourlyDataFlow::whereNodeId(null)
            ->where('total', '>', MB * 50)
            ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))
            ->groupBy('user_id')
            ->selectRaw('user_id, sum(total) as totalTraffic')
            ->get(); // 只统计100M以上的记录，加快查询速度
        $trafficBanValue = sysConfig('traffic_ban_value');

        foreach ($userTotalTrafficLogs->load('user') as $log) {
            // 推送通知管理员
            if ($log->totalTraffic > $trafficBanValue * GB) {
                $user = $log->user;
                $traffic = UserHourlyDataFlow::userRecentUsed($user->id)
                    ->selectRaw('user_id, sum(`u`) as totalU, sum(`d`) as totalD, sum(total) as totalTraffic')
                    ->first();

                Notification::send(User::permission('admin.user.edit,update')->orWhere(function ($query) {
                    return $query->role('Super Admin');
                })->get(), new DataAnomaly($user->id, flowAutoShow($traffic->totalU), flowAutoShow($traffic->totalD), flowAutoShow($traffic->totalTraffic)));
            }
        }
    }
}
