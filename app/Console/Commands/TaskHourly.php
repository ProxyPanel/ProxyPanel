<?php

namespace App\Console\Commands;

use App\Models\NodeHourlyDataFlow;
use App\Models\User;
use App\Models\UserDataFlowLog;
use App\Models\UserHourlyDataFlow;
use App\Notifications\DataAnomaly;
use DB;
use Illuminate\Console\Command;
use Log;
use Notification;

class TaskHourly extends Command
{
    protected $signature = 'task:hourly {--at= : 回填指定小时的统计，如 "2026-10-08 20:00"；缺省统计上一个整点小时}';

    protected $description = '每小时任务';

    public function handle(): int
    {
        $jobTime = microtime(true);
        $createdAt = $this->statWindow();

        if ($createdAt === null) {
            $this->error('--at 需要能解析成时间，例如 "2026-10-08 20:00"');

            return self::FAILURE;
        }

        // 窗口无原始日志就跳过：静默小时没有行可写，而回填已超出流量日志保留期的旧时段时，delete 会抹掉清理前落下的行却无从重建
        if (! $this->windowHasLogs($createdAt)) {
            return self::SUCCESS;
        }

        $anomalies = [];
        // 聚合必须在事务外算完：每插一行都经外键给 user / node 父行加 S 锁并持有到提交，
        // 把窗口内的分组查询留在事务里就是让并发上报等锁（形状与 TaskDaily 同一条判据）
        $userRows = $this->userTrafficRows($createdAt, $anomalies);
        $nodeRows = $this->nodeTrafficRows($createdAt);

        // 原子替换整个窗口：写入走 insert 且没有唯一键兜底，重跑/回填前先在同事务里清掉旧行；
        // 中途失败整体回滚，不会留下「旧的已删、新的没写完」的空洞
        DB::transaction(function () use ($createdAt, $userRows, $nodeRows) {
            UserHourlyDataFlow::where('created_at', $createdAt)->delete();
            NodeHourlyDataFlow::where('created_at', $createdAt)->delete();

            foreach (array_chunk($userRows, 500) as $slice) {
                UserHourlyDataFlow::insert($slice);
            }

            foreach (array_chunk($nodeRows, 500) as $slice) {
                NodeHourlyDataFlow::insert($slice);
            }
        });

        // 告警留到提交之后再发：事务回滚时不会推送针对已回滚数据的告警
        $this->alertAnomalies($anomalies);

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));

        return self::SUCCESS;
    }

    /** 统计窗口的右端点：默认上一个整点小时的 59:59 */
    private function statWindow(): ?string
    {
        $at = (string) $this->option('at');

        if ($at === '') {
            return date('Y-m-d H:59:59', strtotime('-1 hour'));
        }

        $time = strtotime($at);

        return $time === false ? null : date('Y-m-d H:59:59', $time);
    }

    /** 窗口内是否还有原始流量日志：无日志时聚合结果为空，也就没有可重建的行，不该 delete 掉可能仍在的旧统计 */
    private function windowHasLogs(string $created_at): bool
    {
        $end = strtotime($created_at);

        return UserDataFlowLog::hasLogsInWindow($end - 3599, $end);
    }

    /**
     * 用户小时流量统计的行：按 uid 升序，插行时的外键 S 锁顺序要与并发上报一致.
     *
     * @return array<int, array<string, mixed>>
     */
    private function userTrafficRows(string $created_at, array &$anomalies): array
    {
        $end = strtotime($created_at);
        $start = $end - 3599;
        // 回填是补历史，不再重发当小时的异常告警
        $data_anomaly_notification = sysConfig('data_anomaly_notification') && ! $this->option('at');
        $traffic_abuse_threshold = (int) sysConfig('traffic_abuse_limit', 20) * GiB;
        $rows = [];

        // 窗口内上报过的活跃账号按「区间 -> uid」取，逐批聚合再按 uid + log_time 走索引
        UserDataFlowLog::activeUserIds($start, $end)->chunk((int) sysConfig('tasks_chunk', 3000))->each(function ($userIds) use ($start, $end, $created_at, $traffic_abuse_threshold, $data_anomaly_notification, &$anomalies, &$rows) {
            // 一小时内同一 (用户,节点) 的多次上报在库侧 SUM 成一行，只把聚合结果读回，不水合每条日志
            $nodeTotals = UserDataFlowLog::query()
                ->whereIn('user_id', $userIds)
                ->whereBetween('log_time', [$start, $end])
                ->groupBy('user_id', 'node_id')
                ->get(['user_id', 'node_id', DB::raw('SUM(u) as u'), DB::raw('SUM(d) as d')]);

            foreach ($nodeTotals->groupBy('user_id') as $userId => $perNode) {
                $uid = (int) $userId;
                $sum_u = 0;
                $sum_d = 0;
                foreach ($perNode as $node) {
                    $u = (int) $node->u;
                    $d = (int) $node->d;
                    $sum_u += $u;
                    $sum_d += $d;
                    $rows[] = ['user_id' => $uid, 'node_id' => (int) $node->node_id, 'u' => $u, 'd' => $d, 'created_at' => $created_at];
                }
                $rows[] = ['user_id' => $uid, 'node_id' => null, 'u' => $sum_u, 'd' => $sum_d, 'created_at' => $created_at]; // 每小时节点流量合计

                if ($data_anomaly_notification && ($sum_u + $sum_d) >= $traffic_abuse_threshold) { // 用户流量异常，攒到事务提交后再发
                    $anomalies[] = [$uid, $sum_u, $sum_d];
                }
            }
        });

        return $rows;
    }

    /** 异常告警统一在事务提交后发；收件人固定是 id=1 的管理员，缺失则跳过而不是让整条任务在数据已落库后崩掉 */
    private function alertAnomalies(array $anomalies): void
    {
        if ($anomalies === [] || ($admin = User::find(1)) === null) {
            return;
        }

        foreach ($anomalies as [$uid, $sum_u, $sum_d]) {
            Notification::send($admin, new DataAnomaly($uid, formatBytes($sum_u), formatBytes($sum_d), formatBytes($sum_u + $sum_d)));
        }
    }

    /**
     * 节点小时流量统计的行：每小时每节点一行，统计全部用户不加活跃过滤.
     *
     * @return array<int, array<string, mixed>>
     */
    private function nodeTrafficRows(string $created_at): array
    {
        $end = strtotime($created_at);
        $start = $end - 3599;

        // 一条分组查询取代「每节点两个 SUM 子查询」；节点合计统计全部用户，不加活跃过滤
        $nodeTotals = UserDataFlowLog::query()
            ->whereBetween('log_time', [$start, $end])
            ->groupBy('node_id')
            ->get(['node_id', DB::raw('SUM(u) as u'), DB::raw('SUM(d) as d')]);

        $rows = [];
        foreach ($nodeTotals as $node) {
            $rows[] = ['node_id' => (int) $node->node_id, 'u' => (int) $node->u, 'd' => (int) $node->d, 'created_at' => $created_at];
        }

        return $rows;
    }
}
