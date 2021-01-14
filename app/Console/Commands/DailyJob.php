<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\PushNotification;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Log;

class DailyJob extends Command
{
    protected $signature = 'dailyJob';
    protected $description = '每日任务';

    public function handle(): void
    {
        $jobStartTime = microtime(true);

        // 过期用户处理
        $this->expireUser();

        // 关闭超过72小时未处理的工单
        $this->closeTickets();

        // 重置用户流量
        if (sysConfig('reset_traffic')) {
            $this->resetUserTraffic();
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    private function expireUser(): void
    {
        // 过期用户处理
        $userList = User::activeUser()->where('expired_at', '<', date('Y-m-d'))->get();
        $isBanStatus = sysConfig('is_ban_status');
        foreach ($userList as $user) {
            if ($isBanStatus) {
                $user->update([
                    'u' => 0,
                    'd' => 0,
                    'transfer_enable' => 0,
                    'enable' => 0,
                    'level' => 0,
                    'reset_time' => null,
                    'ban_time' => null,
                    'status' => -1,
                ]);

                Helpers::addUserBanLog($user->id, 0, '【禁止登录，清空账户】-账号已过期');

                // 废除其名下邀请码
                $user->invites()->whereStatus(0)->update(['status' => 2]);

                // 写入用户流量变动记录
                Helpers::addUserTrafficModifyLog($user->id, null, $user->transfer_enable, 0, '[定时任务]账号已过期(禁止登录，清空账户)');
            } else {
                $user->update([
                    'u' => 0,
                    'd' => 0,
                    'transfer_enable' => 0,
                    'enable' => 0,
                    'level' => 0,
                    'reset_time' => null,
                    'ban_time' => null,
                ]);

                Helpers::addUserBanLog($user->id, 0, '【封禁代理，清空账户】-账号已过期');

                // 写入用户流量变动记录
                Helpers::addUserTrafficModifyLog($user->id, null, $user->transfer_enable, 0, '[定时任务]账号已过期(封禁代理，清空账户)');
            }
        }
    }

    // 关闭超过72小时未处理的工单
    private function closeTickets(): void
    {
        foreach (Ticket::where('updated_at', '<=', date('Y-m-d', strtotime('-3 days')))->whereStatus(1)->get() as $ticket) {
            if ($ticket->close()) {
                PushNotification::send('工单关闭提醒', '工单：ID'.$ticket->id.'超过72小时未处理，系统已自动关闭');
            }
        }
    }

    // 重置用户流量
    private function resetUserTraffic(): void
    {
        $userList = User::where('status', '<>', -1)
            ->where('expired_at', '>', date('Y-m-d'))
            ->where('reset_time', '<=', date('Y-m-d'))
            ->get();
        foreach ($userList as $user) {
            // 跳过 没有重置日期的账号
            if (! $user->reset_time) {
                continue;
            }

            // 取出用户正在使用的套餐
            $order = Order::userActivePlan($user->id)->first();

            // 无订单用户跳过
            if (! $order) {
                continue;
            }

            // 过期生效中的加油包
            Order::userActivePackage($user->id)->update(['is_expire' => 1]);

            $oldData = $user->transfer_enable;
            // 重置流量与重置日期
            $ret = $user->update((new OrderService($order))->resetTimeAndData($user->expired_at));
            if ($ret) {
                // 可用流量变动日志
                Helpers::addUserTrafficModifyLog($order->user_id, $order->id, $oldData, $user->transfer_enable, '【流量重置】重置可用流量');
                Log::info('用户[ID：'.$user->id.'  昵称： '.$user->username.'  邮箱： '.$user->email.'] 流量重置为 '.flowAutoShow($user->transfer_enable).'. 重置日期为 '.($user->reset_time ?: '【无】'));
            } else {
                Log::warning('用户[ID：'.$user->id.'  昵称： '.$user->username.'  邮箱： '.$user->email.'] 流量重置失败');
            }
        }
    }
}
