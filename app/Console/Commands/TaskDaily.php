<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketClosed;
use App\Services\OrderService;
use App\Utils\Helpers;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Log;

class TaskDaily extends Command
{
    protected $signature = 'task:daily';

    protected $description = '每日任务';

    public function handle(): void
    {
        $jobTime = microtime(true);

        $this->expireUser(); // 过期用户处理
        $this->closeTickets(); // 关闭用户超时未处理的工单
        if (sysConfig('reset_traffic')) {
            $this->resetUserTraffic(); // 重置用户流量
        }
        if (sysConfig('auto_release_port')) {
            $this->releaseAccountPort();  // 账号端口回收
        }
        $this->userTrafficStatistics(); // 用户每日流量统计
        $this->nodeTrafficStatistics(); // 节点每日流量统计

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    private function expireUser(): void
    { // 过期用户处理
        $isBanStatus = sysConfig('is_ban_status');
        User::activeUser()
            ->where('expired_at', '<', date('Y-m-d')) // 过期
            ->chunk(config('tasks.chunk'), function ($users) use ($isBanStatus) {
                $dirtyWorks = [
                    'u' => 0,
                    'd' => 0,
                    'transfer_enable' => 0,
                    'enable' => 0,
                    'level' => 0,
                    'reset_time' => null,
                    'ban_time' => null,
                ]; // 停止服务
                $banMsg = __('[Daily Task] Account Expiration: Stop Service');
                if ($isBanStatus) {
                    $dirtyWorks['status'] = -1; // 封禁账号
                    $banMsg = __('[Daily Task] Account Expiration: Block Login & Clear Account');
                }
                foreach ($users as $user) {
                    $user->update($dirtyWorks);
                    Helpers::addUserTrafficModifyLog($user->id, $user->transfer_enable, 0, $banMsg);
                    $user->banedLogs()->create(['description' => $banMsg]);
                    if ($isBanStatus) {
                        $user->invites()->whereStatus(0)->update(['status' => 2]); // 废除其名下邀请码
                    }
                }
            });
    }

    private function closeTickets(): void
    { // 关闭用户超时未处理的工单
        Ticket::whereStatus(1)
            ->whereHas('reply', function ($q) {
                $q->where('admin_id', '<>', null);
            })
            ->has('reply')
            ->where('updated_at', '<=', date('Y-m-d', strtotime('-'.config('tasks.close.tickets').' hours')))
            ->chunk(config('tasks.chunk'), function ($tickets) {
                foreach ($tickets as $ticket) {
                    if ($ticket->close()) {
                        $ticket->user->notify(new TicketClosed($ticket->id, $ticket->title,
                            route('replyTicket', ['id' => $ticket->id]),
                            __('You have not responded this ticket in :num hours, System has closed your ticket.', ['num' => config('tasks.close.tickets')])));
                    }
                }
            });
    }

    private function resetUserTraffic(): void
    { // 重置用户流量
        User::where('status', '<>', -1)
            ->where('expired_at', '>', date('Y-m-d'))
            ->where('reset_time', '<=', date('Y-m-d'))
            ->with('orders')->whereHas('orders')
            ->chunk(config('tasks.chunk'), function ($users) {
                foreach ($users as $user) {
                    $order = $user->orders()->activePlan()->first(); // 取出用户正在使用的套餐

                    if (! $order) {// 无套餐用户跳过
                        Log::error('用户[ID：'.$user->id.'] 流量重置, 用户订单获取失败');

                        continue;
                    }

                    $user->orders()->activePackage()->update(['is_expire' => 1]); // 过期生效中的加油包

                    $oldData = $user->transfer_enable;
                    // 重置流量与重置日期
                    if ($user->update((new OrderService($order))->resetTimeAndData($user->expired_at))) {
                        Helpers::addUserTrafficModifyLog($order->user_id, $oldData, $user->transfer_enable, __('[Daily Task] Reset Account Traffic, Next Reset Date: :date', ['date' => $user->reset_date]), $order->id);
                    } else {
                        Log::error("[每日任务]用户ID: $user->id | 邮箱: $user->username 流量重置失败");
                    }
                }
            });
    }

    private function releaseAccountPort(): void
    { // 被封禁 / 过期N天 的账号自动释放端口
        User::where('port', '<>', 0)
            ->where(function ($query) {
                $query->whereStatus(-1)->orWhere('expired_at', '<=', date('Y-m-d', strtotime('-'.config('tasks.release_port').' days')));
            })
            ->update(['port' => 0]);
    }

    private function userTrafficStatistics(): void
    {
        $created_at = date('Y-m-d 23:59:59', strtotime('-1 days'));
        $end = strtotime($created_at);
        $start = $end - 86399;
        // todo: laravel10 得改
        User::activeUser()->with('dataFlowLogs')->whereHas('dataFlowLogs', function (Builder $query) use ($start, $end) {
            $query->whereBetween('log_time', [$start, $end]);
        })->chunk(config('tasks.chunk'), function ($users) use ($start, $end, $created_at) {
            foreach ($users as $user) {
                $logs = $user->dataFlowLogs()
                    ->whereBetween('log_time', [$start, $end])
                    ->groupBy('node_id')
                    ->selectRaw('node_id, sum(`u`) as u, sum(`d`) as d')
                    ->get();

                $data = $logs->each(function ($log) use ($created_at) {
                    $log->total = $log->u + $log->d;
                    $log->traffic = formatBytes($log->total);
                    $log->created_at = $created_at;
                })->flatten()->toArray();

                $data[] = [ // 每日节点流量合计
                    'u' => $logs->sum('u'),
                    'd' => $logs->sum('d'),
                    'total' => $logs->sum('total'),
                    'traffic' => formatBytes($logs->sum('total')),
                    'created_at' => $created_at,
                ];

                $user->dailyDataFlows()->createMany($data);
            }
        });
    }

    private function nodeTrafficStatistics(): void
    {
        $created_at = date('Y-m-d 23:59:59', strtotime('-1 day'));
        $end = strtotime($created_at);
        $start = $end - 86399;

        Node::orderBy('id')->with('userDataFlowLogs')->whereHas('userDataFlowLogs', function (Builder $query) use ($start, $end) {
            $query->whereBetween('log_time', [$start, $end]);
        })->chunk(config('tasks.chunk'), function ($nodes) use ($start, $end, $created_at) {
            foreach ($nodes as $node) {
                $traffic = $node->userDataFlowLogs()
                    ->whereBetween('log_time', [$start, $end])
                    ->selectRaw('sum(`u`) as u, sum(`d`) as d')->first();
                $total = $traffic->u + $traffic->d;
                $node->dailyDataFlows()->create([
                    'u' => $traffic->u,
                    'd' => $traffic->d,
                    'total' => $total,
                    'traffic' => formatBytes($total),
                    'created_at' => $created_at,
                ]);
            }
        });
    }
}
