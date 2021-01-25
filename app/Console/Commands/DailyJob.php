<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketClosed;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Log;

class DailyJob extends Command
{
    protected $signature = 'dailyJob';
    protected $description = '每日任务';

    public function handle()
    {
        $jobStartTime = microtime(true);

        $this->expireUser(); // 过期用户处理
        $this->closeTickets(); // 关闭超时未处理的工单

        if (sysConfig('reset_traffic')) {// 重置用户流量
            $this->resetUserTraffic();
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    private function expireUser()// 过期用户处理
    {
        $isBanStatus = sysConfig('is_ban_status');
        User::activeUser()
            ->where('expired_at', '<', date('Y-m-d'))
            ->chunk(config('tasks.chunk'), function ($users) use ($isBanStatus) {
                foreach ($users as $user) {
                    if ($isBanStatus) { // 停止服务 或 封禁账号
                        $user->update([
                            'u'               => 0,
                            'd'               => 0,
                            'transfer_enable' => 0,
                            'enable'          => 0,
                            'level'           => 0,
                            'reset_time'      => null,
                            'ban_time'        => null,
                            'status'          => -1,
                        ]);

                        Helpers::addUserBanLog($user->id, 0, '【禁止登录，清空账户】-账号已过期');

                        // 废除其名下邀请码
                        $user->invites()->whereStatus(0)->update(['status' => 2]);

                        // 写入用户流量变动记录
                        Helpers::addUserTrafficModifyLog($user->id, null, $user->transfer_enable, 0, '[定时任务]账号已过期(禁止登录，清空账户)');
                    } else {
                        $user->update([
                            'u'               => 0,
                            'd'               => 0,
                            'transfer_enable' => 0,
                            'enable'          => 0,
                            'level'           => 0,
                            'reset_time'      => null,
                            'ban_time'        => null,
                        ]);

                        Helpers::addUserBanLog($user->id, 0, '【封禁代理，清空账户】-账号已过期');

                        // 写入用户流量变动记录
                        Helpers::addUserTrafficModifyLog($user->id, null, $user->transfer_enable, 0, '[定时任务]账号已过期(封禁代理，清空账户)');
                    }
                }
            });
    }

    private function closeTickets()// 关闭超时未处理的工单
    {
        Ticket::whereStatus(1)
            ->where('updated_at', '<=', date('Y-m-d', strtotime('-'.config('tasks.close.ticket').' hours')))
            ->chunk(config('tasks.chunk'), function ($tickets) {
                foreach ($tickets as $ticket) {
                    if ($ticket->close()) {
                        $ticket->user->notify(new TicketClosed($ticket->id, $ticket->title, route('replyTicket', ['id' => $ticket->id]),
                            __('You have not responded this ticket in :num hours, System has auto closed your ticket.', ['num' => config('tasks.close.ticket')])));
                    }
                }
            });
    }

    private function resetUserTraffic()// 重置用户流量
    {
        User::where('status', '<>', -1)
            ->where('expired_at', '>', date('Y-m-d'))
            ->where('reset_time', '<=', date('Y-m-d'))
            ->whereNotNull('reset_time')
            ->with('order')->whereHas('order')
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    $order = $user->orders()->activePlan()->first(); // 取出用户正在使用的套餐

                    if (! $order) {// 无套餐用户跳过
                        continue;
                    }

                    $user->order()->activePackage()->update(['is_expire' => 1]); // 过期生效中的加油包

                    $oldData = $user->transfer_enable;
                    // 重置流量与重置日期
                    if ($user->update((new OrderService($order))->resetTimeAndData($user->expired_at))) {
                        // 可用流量变动日志
                        Helpers::addUserTrafficModifyLog($order->user_id, $order->id, $oldData, $user->transfer_enable, '【流量重置】重置可用流量');
                        Log::info('用户[ID：'.$user->id.'  昵称： '.$user->username.'  邮箱： '.$user->email.'] 流量重置为 '.flowAutoShow($user->transfer_enable).'. 重置日期为 '.($user->reset_time ?: '【无】'));
                    } else {
                        Log::warning('用户[ID：'.$user->id.'  昵称： '.$user->username.'  邮箱： '.$user->email.'] 流量重置失败');
                    }
                }
            });
    }
}
