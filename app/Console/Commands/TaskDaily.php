<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketClosed;
use App\Services\OrderService;
use App\Utils\Helpers;
use DB;
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

        $dirtyWorks = [
            'u' => 0,
            'd' => 0,
            'transfer_enable' => 0,
            'enable' => 0,
            'level' => 0,
            'reset_time' => null,
            'ban_time' => null,
        ]; // 清理账号 & 停止服务

        $banMsg = __('[Daily Task] Account Expiration: Stop Service');
        if ($isBanStatus) {
            $dirtyWorks['status'] = -1; // 封禁账号
            $banMsg = __('[Daily Task] Account Expiration: Block Login & Clear Account');
        }

        User::activeUser()->where('expired_at', '<', date('Y-m-d')) // 过期
            ->chunk(config('tasks.chunk'), function ($users) use ($banMsg, $dirtyWorks) {
                $users->each(function ($user) use ($banMsg, $dirtyWorks) {
                    $user->update($dirtyWorks);
                    Helpers::addUserTrafficModifyLog($user->id, $user->transfer_enable, 0, $banMsg);
                    $user->banedLogs()->create(['description' => $banMsg]);
                });
            });
    }

    private function closeTickets(): void
    { // 关闭用户超时未处理的工单
        $closeTicketsHours = config('tasks.close.tickets');

        Ticket::whereStatus(1)->with('reply')->whereHas('reply', function ($query) {
            $query->where('admin_id', '<>', null);
        })->where('updated_at', '<=', now()->subHours($closeTicketsHours))->chunk(config('tasks.chunk'), function ($tickets) use ($closeTicketsHours) {
            $tickets->each(function ($ticket) use ($closeTicketsHours) {
                if ($ticket->close()) {
                    $ticket->user->notify(new TicketClosed($ticket->id, $ticket->title, route('replyTicket', ['id' => $ticket->id]),
                        __('You have not responded this ticket in :num hours, System has closed your ticket.', ['num' => $closeTicketsHours])));
                }
            });
        });
    }

    private function resetUserTraffic(): void
    { // 重置用户流量
        $today = date('Y-m-d');
        User::where('status', '<>', -1)->where('expired_at', '>', $today)->where('reset_time', '<=', $today)->with([
            'orders' => function ($query) {
                $query->activePlan();
            },
        ])->has('orders')->chunk(config('tasks.chunk'), function ($users) {
            $users->each(function ($user) {
                $user->orders()->activePackage()->update(['is_expire' => 1]); // 过期生效中的加油包
                $order = $user->orders()->activePlan()->first(); // 取出用户正在使用的套餐

                $oldData = $user->transfer_enable;
                // 重置流量与重置日期
                if ($user->update((new OrderService($order))->resetTimeAndData($user->expired_at))) {
                    Helpers::addUserTrafficModifyLog($order->user_id, $oldData, $user->transfer_enable, __('[Daily Task] Reset Account Traffic, Next Reset Date: :date', ['date' => $user->reset_date]), $order->id);
                } else {
                    Log::error("[每日任务]用户ID: $user->id | 邮箱: $user->username 流量重置失败");
                }
            });
        });
    }

    private function releaseAccountPort(): void
    { // 被封禁 / 过期N天 的账号自动释放端口
        User::where('port', '<>', 0)->where(function (Builder $query) {
            $query->whereStatus(-1)->orWhere('expired_at', '<=', date('Y-m-d', strtotime('-'.config('tasks.release_port').' days')));
        })->update(['port' => 0]);
    }

    private function userTrafficStatistics(): void
    {
        $created_at = date('Y-m-d 23:59:59', strtotime('yesterday'));
        $end = strtotime($created_at);
        $start = $end - 86399;

        User::activeUser()->whereHas('dataFlowLogs', function (Builder $query) use ($start, $end) {
            $query->whereBetween('log_time', [$start, $end]);
        })->with([
            'dataFlowLogs' => function ($query) use ($start, $end) {
                $query->whereBetween('log_time', [$start, $end]);
            },
        ])->chunk(config('tasks.chunk'), function ($users) use ($created_at) {
            foreach ($users as $user) {
                $dataFlowLogs = $user->dataFlowLogs->groupBy('node_id');

                $data = $dataFlowLogs->map(function ($logs, $nodeId) use ($created_at) {
                    $totals = $logs->reduce(function ($carry, $log) {
                        $carry['u'] += $log['u'];
                        $carry['d'] += $log['d'];

                        return $carry;
                    }, ['u' => 0, 'd' => 0]);

                    return [
                        'node_id' => $nodeId,
                        'u' => $totals['u'],
                        'd' => $totals['d'],
                        'created_at' => $created_at,
                    ];
                })->values()->all();

                $data[] = [ // 每日节点流量合计
                    'node_id' => null,
                    'u' => array_sum(array_column($data, 'u')),
                    'd' => array_sum(array_column($data, 'd')),
                    'created_at' => $created_at,
                ];

                $user->dailyDataFlows()->createMany($data);
            }
        });
    }

    private function nodeTrafficStatistics(): void
    {
        $created_at = date('Y-m-d 23:59:59', strtotime('yesterday'));
        $end = strtotime($created_at);
        $start = $end - 86399;

        Node::whereHas('userDataFlowLogs', function (Builder $query) use ($start, $end) {
            $query->whereBetween('log_time', [$start, $end]);
        })->withCount([
            'userDataFlowLogs as u_sum' => function ($query) use ($start, $end) {
                $query->select(DB::raw('SUM(u)'))->whereBetween('log_time', [$start, $end]);
            },
        ])->withCount([
            'userDataFlowLogs as d_sum' => function ($query) use ($start, $end) {
                $query->select(DB::raw('SUM(d)'))->whereBetween('log_time', [$start, $end]);
            },
        ])->chunk(config('tasks.chunk'), function ($nodes) use ($created_at) {
            foreach ($nodes as $node) {
                $node->dailyDataFlows()->create([
                    'u' => $node->u_sum,
                    'd' => $node->d_sum,
                    'created_at' => $created_at,
                ]);
            }
        });
    }
}
