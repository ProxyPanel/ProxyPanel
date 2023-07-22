<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function accounting()
    {
        $orders = Order::where('status', '>=', 2)->has('goods')->latest()->get(['created_at', 'amount']);
        $ordersByDay = $orders->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($row) {
            return $row->sum('amount');
        })->toArray();

        $ordersByMonth = $orders->groupBy(function ($item) {
            return $item->created_at->format('Y-m');
        })->map(function ($row) {
            return $row->sum('amount');
        })->toArray();

        $ordersByYear = $orders->groupBy(function ($item) {
            return $item->created_at->format('Y');
        })->map(function ($row) {
            return $row->sum('amount');
        })->sort()->toArray();

        $currentDays = date('j');
        $lastDays = date('t', strtotime('-1 months'));
        $data['days'] = range(1, max($currentDays, $lastDays));
        $data['years'] = range(1, 12);

        for ($i = 1; $i <= $currentDays; $i++) {
            $data['currentMonth'][] = $ordersByDay[date(sprintf('Y-m-%02u', $i))] ?? 0;
        }

        for ($i = 1; $i <= $lastDays; $i++) {
            $data['lastMonth'][] = $ordersByDay[date(sprintf('Y-m-%02u', $i), strtotime('-1 months'))] ?? 0;
        }

        for ($i = 1; $i <= date('m'); $i++) {
            $data['currentYear'][] = $ordersByMonth[date(sprintf('Y-%02u', $i))] ?? 0;
        }

        for ($i = 1; $i <= 12; $i++) {
            $data['lastYear'][] = $ordersByMonth[date(sprintf('Y-%02u', $i), strtotime('-1 years'))] ?? 0;
        }

        ksort($ordersByYear);
        $data['ordersByYear'] = $ordersByYear;

        return view('admin.report.accounting', compact('data'));
    }

    public function userAnalysis(Request $request)
    {
        $uid = $request->input('uid');
        $username = $request->input('username');
        if ($uid) {
            $user = User::find($uid);
        } elseif ($username) {
            $user = User::whereUsername($username)->first();
        }

        $data = null;
        if (isset($user)) {
            // 用户当前小时在各线路消耗流量
            $data['currentHourlyFlow'] = $user->dataFlowLogs()
                ->where('log_time', '>=', now()->startOfHour()->timestamp)
                ->groupBy('node_id')
                ->selectRaw('node_id, sum(u + d) as total')
                ->get()->toArray();

            // 用户今天各小时在各线路消耗流量
            $data['hours'] = range(0, 23);
            $data['hourlyFlow'] = $user->hourlyDataFlows()->whereNotNull('node_id')
                ->whereDate('created_at', now())
                ->selectRaw('node_id, (DATE_FORMAT(user_hourly_data_flow.created_at, "%k")) as date, u + d as total')
                ->get()->transform(function ($item) {
                    return [
                        'node_id' => $item->node_id,
                        'date' => (int) $item->date,
                        'total' => round($item->total / GiB, 2),
                    ];
                })->toArray();

            // 用户本月每天在各线路消耗流量
            $data['days'] = range(1, date('j'));
            $data['dailyFlow'] = $user->dailyDataFlows()->whereNotNull('node_id')
                ->whereMonth('created_at', date('n'))
                ->selectRaw('node_id, (DATE_FORMAT(user_daily_data_flow.created_at, "%e")) as date, u + d as total')
                ->get()->transform(function ($item) {
                    return [
                        'node_id' => $item->node_id,
                        'date' => (int) $item->date,
                        'total' => round($item->total / GiB, 2),
                    ];
                })->toArray();
        }

        return view('admin.report.userDataAnalysis', compact('data'));
    }
}
