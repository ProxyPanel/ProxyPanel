<?php

namespace App\Helpers;

use App\Models\NodeDailyDataFlow;
use App\Models\NodeHourlyDataFlow;
use App\Models\UserDailyDataFlow;
use App\Models\UserDataFlowLog;
use App\Models\UserHourlyDataFlow;
use DB;

trait DataChart
{
    /**
     * 流量统计
     *
     * @param  int  $id  用户ID 或者 节点ID
     * @param  bool  $is_node  决定 id 是否为节点ID
     * @return array 用户/节点 流量统计
     */
    public function dataFlowChart(int $id, bool $is_node = false): array // 流量使用图表
    {
        $lastHour = (int) date('G') + 1;
        $lastDay = date('j');
        $hourlyData = array_fill(0, $lastHour, 0);
        $dailyData = array_fill(0, $lastDay - 1, 0);

        if ($is_node) {
            $currentFlow = UserDataFlowLog::whereNodeId($id);
            $hourlyFlow = NodeHourlyDataFlow::whereNodeId($id)->whereDate('created_at', date('Y-m-d'))->selectRaw('(DATE_FORMAT(node_hourly_data_flow.created_at, "%k")) as date, u + d as total')->pluck('total', 'date');
            $dailyFlow = NodeDailyDataFlow::whereNodeId($id)->whereMonth('created_at', date('n'))->selectRaw('(DATE_FORMAT(node_daily_data_flow.created_at, "%e")) as date, u + d as total')->pluck('total', 'date');
        } else {
            $currentFlow = UserDataFlowLog::whereUserId($id);
            $hourlyFlow = UserHourlyDataFlow::userHourly($id)->whereDate('created_at', date('Y-m-d'))->selectRaw('(DATE_FORMAT(user_hourly_data_flow.created_at, "%k")) as date, u + d as total')->pluck('total', 'date');
            $dailyFlow = UserDailyDataFlow::userDaily($id)->whereMonth('created_at', date('n'))->selectRaw('(DATE_FORMAT(user_daily_data_flow.created_at, "%e")) as date, u + d as total')->pluck('total', 'date');
        }
        $currentFlow = $currentFlow->where('log_time', '>=', now()->startOfHour()->timestamp)->sum(DB::raw('u + d'));

        // 节点一天内的流量
        foreach ($hourlyFlow as $date => $dataFlow) {
            $hourlyData[$date] = round($dataFlow / GiB, 3);
        }
        $hourlyData[$lastHour] = round($currentFlow / GiB, 3);

        // 节点一个月内的流量
        foreach ($dailyFlow as $date => $dataFlow) {
            $dailyData[$date - 1] = round($dataFlow / GiB, 3);
        }

        $dailyData[$lastDay - 1] = round(array_sum($hourlyData) + $currentFlow / GiB, 3);

        return [
            'trafficDaily' => $dailyData,
            'trafficHourly' => $hourlyData,
            'monthDays' => range(1, $lastDay), // 本月天数
            'dayHours' => range(0, $lastHour), // 本日小时
        ];
    }
}
