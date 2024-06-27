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
        $jobTime = microtime(true);

        $this->expireUser(); // 过期用户处理
        $this->closeTickets(); // 关闭用户超时未处理的工单

        if (sysConfig('reset_traffic')) {// 重置用户流量
            $this->resetUserTraffic();
        }

        $jobTime = round(microtime(true) - $jobTime, 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobTime.'秒');
    }

    private function expireUser()// 过期用户处理
    {
        $isBanStatus = sysConfig('is_ban_status');
        User::activeUser()
            ->where('expired_at', '<', date('Y-m-d'))
            ->chunk(config('tasks.chunk'), function ($users) use ($isBanStatus) {
                foreach ($users as $user) {
                    if ($isBanStatus) {
                        $user->update([ // 停止服务 & 封禁账号
                            'u'               => 0,
                            'd'               => 0,
                            'transfer_enable' => 0,
                            'enable'          => 0,
                            'level'           => 0,
                            'reset_time'      => null,
                            'ban_time'        => null,
                            'status'          => -1,
                        ]);
                        $user->banedLogs()->create(['description' => '【禁止登录，清空账户】-账号已过期']);

                        // 废除其名下邀请码
                        $user->invites()->whereStatus(0)->update(['status' => 2]);

                        // 写入用户流量变动记录
                        Helpers::addUserTrafficModifyLog($user->id, null, $user->transfer_enable, 0, '[定时任务]账号已过期(禁止登录，清空账户)');
                    } else {
                        $user->update([ // 停止服务
                            'u'               => 0,
                            'd'               => 0,
                            'transfer_enable' => 0,
                            'enable'          => 0,
                            'level'           => 0,
                            'reset_time'      => null,
                            'ban_time'        => null,
                        ]);
                        $user->banedLogs()->create(['description' => '【封禁代理，清空账户】-账号已过期']);

                        // 写入用户流量变动记录
                        Helpers::addUserTrafficModifyLog($user->id, null, $user->transfer_enable, 0, '[定时任务]账号已过期(封禁代理，清空账户)');
                    }
                }
            });
    }

    private function closeTickets()// 关闭用户超时未处理的工单
    {
        Ticket::whereStatus(1)
            ->whereHas('reply', function ($q) {
                $q->where('admin_id', '<>', null);
            })
            ->has('reply')
            ->where('updated_at', '<=', date('Y-m-d', strtotime('-'.config('tasks.close.tickets').' hours')))
            ->chunk(config('tasks.chunk'), function ($tickets) {
                foreach ($tickets as $ticket) {
                    if ($ticket->close()) {
                        $ticket->user->notify(new TicketClosed($ticket->id, $ticket->title, route('replyTicket', ['id' => $ticket->id]),
                            __('You have not responded this ticket in :num hours, System has closed your ticket.', ['num' => config('tasks.close.tickets')])));
                    }
                }
            });
    }

    private function resetUserTraffic()// 重置用户流量
    {
        User::where('status', '<>', -1)
            ->where('expired_at', '>', date('Y-m-d'))
            ->whereNotNull('reset_time')
            ->where('reset_time', '<=', date('Y-m-d'))
            ->with('orders')->whereHas('orders')
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    $order = $user->orders()->activePlan()->first(); // 取出用户正在使用的套餐

                    if (! $order) {// 无套餐用户跳过
                        continue;
                    }

                    $user->orders()->activePackage()->update(['is_expire' => 1]); // 过期生效中的加油包

                    $oldData = $user->transfer_enable;
                    // 重置流量与重置日期
                    if ($user->update((new OrderService($order))->resetTimeAndData($user->expired_at))) {
                        // 可用流量变动日志
                        Helpers::addUserTrafficModifyLog($order->user_id, $order->id, $oldData, $user->transfer_enable, '【流量重置任务】重置可用流量');
                        Log::notice('用户[ID：'.$user->id.'  昵称： '.$user->nickname.'  邮箱： '.$user->username.'] 流量重置为 '.flowAutoShow($user->transfer_enable).'. 重置日期为 '.
                        ($user->reset_date ?: '【无】'));
                    } else {
                        Log::alert('用户[ID：'.$user->id.'  昵称： '.$user->nickname.'  邮箱： '.$user->username.'] 流量重置失败');
                    }
                }
            });
    }
}
