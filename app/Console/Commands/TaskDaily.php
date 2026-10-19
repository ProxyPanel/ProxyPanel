<?php

namespace App\Console\Commands;

use App\Models\NodeDailyDataFlow;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserDailyDataFlow;
use App\Models\UserDataFlowLog;
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

        $createdAt = date('Y-m-d 23:59:59', strtotime('yesterday'));
        // 昨日窗口没有原始日志就跳过：没有行可重建，而合计行会从空集聚合出 NULL，撞 NOT NULL 列（1048）把整段回滚掉
        if ($this->windowHasLogs($createdAt)) {
            // 聚合必须在事务外算完：每插一行都经外键给 user / node 父行加 S 锁并持有到提交，
            // 把分组查询留在事务里就是让各节点的每分钟上报和后台改节点一起等满锁
            ['users' => $userRows, 'nodes' => $nodeRows] = $this->trafficRows($createdAt);

            // 原子替换昨日统计：重跑同一天不会翻倍（含 node_id=null 的全站日合计行），中途失败整体回滚
            DB::transaction(function () use ($createdAt, $userRows, $nodeRows) {
                UserDailyDataFlow::where('created_at', $createdAt)->delete();
                NodeDailyDataFlow::where('created_at', $createdAt)->delete();

                foreach (array_chunk($userRows, 500) as $slice) {
                    UserDailyDataFlow::insert($slice);
                }

                foreach (array_chunk($nodeRows, 500) as $slice) {
                    NodeDailyDataFlow::insert($slice);
                }
            });
        }

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
            ->chunkById((int) sysConfig('tasks_chunk', 3000), function ($users) use ($banMsg, $dirtyWorks) {
                $users->each(function ($user) use ($banMsg, $dirtyWorks) {
                    $user->update($dirtyWorks);
                    Helpers::addUserTrafficModifyLog($user->id, $user->transfer_enable, 0, $banMsg);
                    $user->banedLogs()->create(['description' => $banMsg]);
                });
            });
    }

    private function closeTickets(): void
    { // 关闭用户超时未处理的工单
        $closeTicketsHours = (time() - strtotime(sysConfig('tasks_close.tickets'))) / 3600;
        Ticket::whereStatus(1)->with('reply')->whereHas('reply', function ($query) {
            $query->where('admin_id', '<>', null);
        })->where('updated_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_close.tickets'))))->chunkById((int) sysConfig('tasks_chunk', 3000), function ($tickets) use ($closeTicketsHours) {
            $tickets->each(function ($ticket) use ($closeTicketsHours) {
                if ($ticket->close()) {
                    $ticket->user->notify(new TicketClosed($ticket->id, $ticket->title, route('ticket.edit', $ticket),
                        __('You have not responded this ticket in :num hours, System has closed your ticket.', ['num' => $closeTicketsHours]), true));
                }
            });
        });
    }

    private function resetUserTraffic(): void
    { // 重置用户流量
        $today = date('Y-m-d');
        User::where('status', '<>', -1)->where('expired_at', '>', $today)->where('reset_time', '<=', $today)->whereHas('orders', function ($query) {
            $query->activePlan();
        })->with(['orders' => function ($query) {
            $query->activePlan();
        }])->chunkById((int) sysConfig('tasks_chunk', 3000), function ($users) {
            $users->each(function ($user) {
                $user->orders()->activePackage()->update(['is_expire' => 1]); // 过期生效中的加油包
                $order = $user->orders->first(); // 取出用户正在使用的套餐

                $oldData = $user->transfer_enable;
                // 重置流量与重置日期
                if ($user->update((new OrderService($order))->resetTimeAndData($user->expired_at))) {
                    Helpers::addUserTrafficModifyLog($order->user_id, $oldData, $user->transfer_enable, trans('[Daily Task] Reset Account Traffic, Next Reset Date: :date', ['date' => $user->reset_date]), $order->id);
                } else {
                    Log::error(trans('notification.reset_failed', ['uid' => $user->id, 'username' => $user->username]));
                }
            });
        });
    }

    private function releaseAccountPort(): void
    { // 被封禁 / 过期N天 的账号自动释放端口
        User::where('port', '<>', 0)->where(function (Builder $query) {
            $query->whereStatus(-1)->orWhere('expired_at', '<=', date('Y-m-d', strtotime('-'.sysConfig('auto_release_port').' days')));
        })->update(['port' => 0]);
    }

    /**
     * 昨日统计的账号行与节点行，由原始日志的一次分片读取折出.
     *
     * 窗口按小时切片，每片一条 (账号,节点) 的库侧分组查询：账号行与节点行都从同一份结果累加，
     * 所以一天只读一遍原始日志，且读多少行始终由窗口大小决定。
     * 账号行只保留活跃账号（与 task:hourly 同一判据），节点行统计全部账号、含被禁用与封禁的。
     * 账号行按 uid 升序、每账号内按 node_id 升序，插行时取 user / node 父行 S 锁的顺序要与并发上报一致。
     *
     * @return array{users: array<int, array<string, mixed>>, nodes: array<int, array<string, mixed>>}
     */
    private function trafficRows(string $created_at): array
    {
        $end = strtotime($created_at);
        $pairs = [];
        $nodes = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $to = $end - Hour * $hour;
            foreach (UserDataFlowLog::pairTotals($to - Hour + 1, $to) as $total) {
                $uid = (int) $total->user_id;
                $nodeId = (int) $total->node_id;
                $pairs[$uid][$nodeId]['u'] = ($pairs[$uid][$nodeId]['u'] ?? 0) + (int) $total->u;
                $pairs[$uid][$nodeId]['d'] = ($pairs[$uid][$nodeId]['d'] ?? 0) + (int) $total->d;
                $nodes[$nodeId]['u'] = ($nodes[$nodeId]['u'] ?? 0) + (int) $total->u;
                $nodes[$nodeId]['d'] = ($nodes[$nodeId]['d'] ?? 0) + (int) $total->d;
            }
        }

        $active = array_flip(User::activeUser()->whereIn('id', array_keys($pairs))->pluck('id')->all());

        $userRows = [];
        ksort($pairs);
        foreach ($pairs as $uid => $perNode) {
            if (! isset($active[$uid])) {
                continue;
            }

            ksort($perNode);
            $sum_u = 0;
            $sum_d = 0;
            foreach ($perNode as $nodeId => $traffic) {
                $userRows[] = ['user_id' => $uid, 'node_id' => $nodeId, 'u' => $traffic['u'], 'd' => $traffic['d'], 'created_at' => $created_at];
                $sum_u += $traffic['u'];
                $sum_d += $traffic['d'];
            }
            $userRows[] = ['user_id' => $uid, 'node_id' => null, 'u' => $sum_u, 'd' => $sum_d, 'created_at' => $created_at]; // 每日节点流量合计
        }

        $nodeRows = [];
        $total_u = 0;
        $total_d = 0;
        ksort($nodes);
        foreach ($nodes as $nodeId => $traffic) {
            $nodeRows[] = ['node_id' => $nodeId, 'u' => $traffic['u'], 'd' => $traffic['d'], 'created_at' => $created_at];
            $total_u += $traffic['u'];
            $total_d += $traffic['d'];
        }

        // 合计的判据取「有没有逐节点行」：不带 GROUP BY 的聚合在空集上照样返回一行 NULL，拿它当守卫等于没守卫
        if ($nodeRows !== []) {
            $nodeRows[] = ['node_id' => null, 'u' => $total_u, 'd' => $total_d, 'created_at' => $created_at];
        }

        return ['users' => $userRows, 'nodes' => $nodeRows];
    }

    /** 昨日窗口内是否还有原始流量日志 */
    private function windowHasLogs(string $created_at): bool
    {
        $end = strtotime($created_at);

        return UserDataFlowLog::hasLogsInWindow($end - 86399, $end);
    }
}
