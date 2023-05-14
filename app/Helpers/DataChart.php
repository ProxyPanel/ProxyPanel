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
    public function dataFlowChart($id, $is_node = false): array // 流量使用图表
    {
        if ($is_node) {
            $currentFlow = UserDataFlowLog::whereNodeId($id);
            $hourlyFlow = NodeHourlyDataFlow::whereNodeId($id)->whereDate('created_at',
                date('Y-m-d'))->selectRaw('(DATE_FORMAT(node_hourly_data_flow.created_at, "%k")) as date, total')->pluck('total', 'date');
            $dailyFlow = NodeDailyDataFlow::whereNodeId($id)->whereMonth('created_at',
                date('n'))->selectRaw('(DATE_FORMAT(node_daily_data_flow.created_at, "%e")) as date, total')->pluck('total', 'date');
        } else {
            $currentFlow = UserDataFlowLog::whereUserId($id);
            $hourlyFlow = UserHourlyDataFlow::userHourly($id)->whereDate('created_at',
                date('Y-m-d'))->selectRaw('(DATE_FORMAT(user_hourly_data_flow.created_at, "%k")) as date, total')->pluck('total', 'date');
            $dailyFlow = UserDailyDataFlow::userDaily($id)->whereMonth('created_at',
                date('n'))->selectRaw('(DATE_FORMAT(user_daily_data_flow.created_at, "%e")) as date, total')->pluck('total', 'date');
        }
        $currentFlow = $currentFlow->where('log_time', '>=', strtotime(date('Y-m-d H:0')))->sum(DB::raw('u + d'));

        // 节点一天内的流量
        $hourlyData = array_fill(0, date('G') + 1, 0);
        foreach ($hourlyFlow as $date => $dataFlow) {
            $hourlyData[$date] = round($dataFlow / GB, 3);
        }
        $hourlyData[date('G') + 1] = round($currentFlow / GB, 3);

        // 节点一个月内的流量
        $dailyData = array_fill(0, date('j') - 1, 0);

        foreach ($dailyFlow as $date => $dataFlow) {
            $dailyData[$date - 1] = round($dataFlow / GB, 3);
        }

        $dailyData[date('j', strtotime(now())) - 1] = round(array_sum($hourlyData) + $currentFlow / GB, 3);

        return [
            'trafficDaily' => $dailyData,
            'trafficHourly' => $hourlyData,
            'monthDays' => range(1, date('j')), // 本月天数
            'dayHours' => range(0, date('G') + 1), // 本日小时
        ];
    }
}
