<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\NodeDailyDataFlow;
use App\Models\NodeHourlyDataFlow;
use App\Models\Order;
use App\Models\User;
use App\Models\UserDataFlowLog;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function accounting(): View
    {
        $completedOrders = Order::where('status', '>=', 2)->has('goods')->selectRaw('DATE(created_at) as date, sum(amount)/100 as total')->groupBy('date')->get();

        $ordersByDay = $completedOrders->filter(fn ($order) => $order->date >= now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'))->pluck('total', 'date');

        $ordersByMonth = $completedOrders->filter(fn ($order) => $order->date >= now()->subYearNoOverflow()->startOfYear())->groupBy(fn ($order) => Carbon::parse($order->date)->format('Y-m'))->map(fn ($rows) => round($rows->sum('total'),
            2))->toArray();

        $ordersByYear = $completedOrders->groupBy(fn ($order) => Carbon::parse($order->date)->format('Y'))->map(fn ($rows) => round($rows->sum('total'), 2))->toArray();

        $currentDays = date('j');
        $lastMonth = strtotime('first day of last month');
        $daysInLastMonth = date('t', $lastMonth);
        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $data = [
            'days' => range(1, max($currentDays, $daysInLastMonth)),
            'months' => array_map(static fn ($month) => Carbon::create(null, $month)->translatedFormat('F'), $months),
            'currentMonth' => array_map(static fn ($i) => round($ordersByDay[date(sprintf('Y-m-%02u', $i))] ?? 0, 2), range(1, $currentDays)),
            'lastMonth' => array_map(static fn ($i) => $ordersByDay[date(sprintf('Y-m-%02u', $i), $lastMonth)] ?? 0, range(1, $daysInLastMonth)),
            'currentYear' => array_map(static fn ($i) => $ordersByMonth[date(sprintf('Y-%02u', $i))] ?? 0, range(1, date('m'))),
            'lastYear' => array_map(static fn ($i) => $ordersByMonth[date(sprintf('Y-%02u', $i), strtotime('-1 years'))] ?? 0, $months),
            'ordersByYear' => $ordersByYear,
        ];

        return view('admin.report.accounting', compact('data'));
    }

    public function userAnalysis(Request $request): View
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
            $currentTime = now();
            $currentDay = $currentTime->day;
            $currentHour = $currentTime->hour;

            // 用户当前小时在各线路消耗流量
            $currentHourFlow = $user->dataFlowLogs()->where('log_time', '>=', $currentTime->startOfHour()->timestamp)->with('node:id,name')->groupBy('node_id')->selectRaw('node_id, log_time, sum(u + d) as total')->get()->map(fn ($item) => [
                'id' => $item->node_id,
                'name' => $item->node->name,
                'time' => $currentHour,
                'total' => round($item->total / MiB, 2),
            ]);

            $hoursFlow = $user->hourlyDataFlows()->whereNotNull('node_id')->whereDate('created_at', $currentTime)->with('node:id,name')->selectRaw('node_id, HOUR(created_at) as hour, u + d as total')->get()->map(fn ($item) => [
                'id' => $item->node_id,
                'name' => $item->node->name,
                'time' => (int) $item->hour,
                'total' => round($item->total / MiB, 2),
            ]); // 用户今天各小时在各线路消耗流量

            $daysFlow = $user->dailyDataFlows()->whereNotNull('node_id')->whereMonth('created_at', $currentTime)->with('node:id,name')->selectRaw('node_id, DAY(created_at) as day, u + d as total')->get()->map(fn ($item) => [
                'id' => $item->node_id,
                'name' => $item->node->name,
                'time' => (int) $item->day,
                'total' => round($item->total / MiB, 2),
            ]);

            $currentDayFlow = collect($currentHourFlow)->merge($hoursFlow)->groupBy('id')->map(fn ($items) => [
                'id' => $items->first()['id'],
                'name' => $items->first()['name'],
                'time' => $currentDay,
                'total' => round($items->sum('total'), 2),
            ])->values();

            $data = [
                'hours' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
                'days' => range(1, $currentDay),
                'nodes' => collect([$currentDayFlow, $daysFlow])->collapse()->pluck('name', 'id')->unique()->toArray(),
                'hourlyFlows' => array_merge($hoursFlow->toArray(), $currentHourFlow->toArray()),
                'dailyFlows' => array_merge($daysFlow->toArray(), $currentDayFlow->toArray()),
            ];
        }

        return view('admin.report.userDataAnalysis', compact('data'));
    }

    public function nodeAnalysis(Request $request)
    {
        $currentTime = now();
        $currentDate = $currentTime->format('m-d');
        $currentHour = $currentTime->hour;
        $nodeId = $request->input('nodes');
        $startDate = $request->input('start') ?? $currentTime->format('Y-m-01');
        $endDate = $request->input('end') ?? $currentTime->format('Y-m-d');
        $hour_date = $request->input('hour_date') ?? $currentTime; // 默认是今天

        $nodes = Node::orderBy('name')->pluck('name', 'id'); // 用于前端节点显示

        $currentHourQuery = UserDataFlowLog::query();
        $hourlyQuery = NodeHourlyDataFlow::query();
        $dailyQuery = NodeDailyDataFlow::query();

        if ($nodeId) { // 节点过滤
            $currentHourQuery->whereIn('node_id', $nodeId);
            $hourlyQuery->whereIn('node_id', $nodeId);
            $dailyQuery->whereIn('node_id', $nodeId);
        }

        $data = [
            'hours' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
            'start_date' => Carbon::parse(NodeDailyDataFlow::orderBy('created_at')->value('created_at'))->format('Y-m-d'), // 数据库里最早的日期
        ];

        $hoursFlow = $hourlyQuery->whereDate('created_at', $hour_date)->selectRaw('node_id, HOUR(created_at) as hour, u + d as total')->get()->map(fn ($item) => [
            'id' => $item->node_id,
            'name' => $nodes[$item->node_id],
            'time' => (int) $item->hour,
            'total' => round($item->total / GiB, 2),
        ])->toArray(); // 各线路小时消耗流量

        $daysFlow = $dailyQuery->whereNotNull('node_id')->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->selectRaw('node_id, DATE_FORMAT(created_at, "%m-%d") as date, u + d as total')->get()->map(fn ($item) => [
            'id' => $item->node_id,
            'name' => $nodes[$item->node_id],
            'time' => $item->date,
            'total' => round($item->total / GiB, 2),
        ])->toArray();

        if (Carbon::parse($hour_date)->isToday()) { // 如果日期是今天，本小时流量需要另外计算
            $currentHourFlow = $currentHourQuery->where('log_time', '>=', $currentTime->startOfHour()->timestamp)->groupBy('node_id')->selectRaw('node_id, sum(u + d) as total')->get()->map(fn ($item) => [
                'id' => $item->node_id,
                'name' => $nodes[$item->node_id],
                'time' => $currentHour,
                'total' => round($item->total / GiB, 2),
            ])->toArray();

            $hoursFlow = array_merge($hoursFlow, $currentHourFlow);

            if (Carbon::parse($endDate)->isToday()) {
                $currentDayFlow = collect($hoursFlow)->groupBy('id')->map(fn ($items) => [
                    'id' => $items->first()['id'],
                    'name' => $items->first()['name'],
                    'time' => $currentDate,
                    'total' => round($items->sum('total'), 2),
                ])->values()->toArray();

                $daysFlow = array_merge($daysFlow, $currentDayFlow);
            }
        } elseif (Carbon::parse($endDate)->isToday()) { // 如果结束日期是今天，本日流量需要另外计算
            $todayHourlyQuery = NodeHourlyDataFlow::query();

            if ($nodeId) { // 节点过滤
                $todayHourlyQuery->whereIn('node_id', $nodeId);
            }

            $hoursFlowToday = $todayHourlyQuery->whereDate('created_at', $currentTime)->selectRaw('node_id, HOUR(created_at) as hour, u + d as total')->get()->map(fn ($item) => [
                'id' => $item->node_id,
                'name' => $nodes[$item->node_id],
                'time' => (int) $item->hour,
                'total' => $item->total / GiB,
            ])->toArray();

            $currentHourFlow = $currentHourQuery->where('log_time', '>=', $currentTime->startOfHour()->timestamp)->groupBy('node_id')->selectRaw('node_id, sum(u + d) as total')->get()->map(fn ($item) => [
                'id' => $item->node_id,
                'name' => $nodes[$item->node_id],
                'time' => $currentHour,
                'total' => $item->total / GiB,
            ])->toArray();

            $currentDayFlow = collect($currentHourFlow)->merge($hoursFlowToday)->groupBy('id')->map(fn ($items) => [
                'id' => $items->first()['id'],
                'name' => $items->first()['name'],
                'time' => $currentDate,
                'total' => round($items->sum('total'), 2),
            ])->values()->toArray();

            $daysFlow = array_merge($daysFlow, $currentDayFlow);
        }

        $data['hourlyFlows'] = $hoursFlow;
        $data['dailyFlows'] = $daysFlow;
        $data['nodes'] = collect($daysFlow)->pluck('name', 'id')->unique()->toArray();
        $hour_dates = NodeHourlyDataFlow::selectRaw('DISTINCT DATE_FORMAT(created_at, "%Y-%m-%d") as formatted_date')->orderByDesc('formatted_date')->pluck('formatted_date')->toArray();

        return view('admin.report.nodeDataAnalysis', compact('data', 'nodes', 'hour_dates'));
    }

    public function siteAnalysis(Request $request): View
    {
        $nodeId = $request->input('node_id');
        $nodes = Node::orderBy('name')->pluck('name', 'id');

        // Fetch flows
        $flows = NodeDailyDataFlow::whereNodeId($nodeId)->selectRaw('DATE(created_at) as date, sum(u + d) as total')->groupBy('date')->get()->keyBy('date');

        $dailyFlows = $flows->filter(fn ($flow) => $flow->date >= now()->subMonthNoOverflow()->startOfMonth()->toDateString())->pluck('total', 'date');

        $monthlyFlows = $flows->groupBy(fn ($flow) => Carbon::parse($flow->date)->format('Y-m'))->map(fn ($rows) => round($rows->sum('total') / GiB, 2));

        $yearlyFlows = $flows->groupBy(fn ($flow) => Carbon::parse($flow->date)->format('Y'))->map(fn ($rows) => round($rows->sum('total') / TiB, 2));

        $currentDays = (int) date('j');
        $lastDays = (int) date('t', strtotime('-1 months'));

        $todayFlow = NodeHourlyDataFlow::whereDate('created_at', today())->when($nodeId, fn ($query) => $query->whereNodeId($nodeId))->sum(DB::raw('u + d')) / GiB;

        $thirtyDaysAgo = now()->subDays(30);
        $trafficData = NodeDailyDataFlow::where('node_id', $nodeId)->where('created_at', '>=', $thirtyDaysAgo)->selectRaw('SUM(u + d) as total, COUNT(*) as dataCounts')->first();

        $total30Days = $trafficData->total ?? 0;

        $daysWithData = max($trafficData->dataCounts ?? 0, 1);
        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        $data = [
            'days' => range(1, max($currentDays, $lastDays)),
            'months' => array_map(static fn ($month) => Carbon::create(null, $month)->translatedFormat('F'), $months),
            'currentMonth' => array_map(static fn ($i) => ($dailyFlows[date(sprintf('Y-m-%02u', $i))] ?? 0) / GiB, range(1, $currentDays)),
            'lastMonth' => array_map(static fn ($i) => ($dailyFlows[date(sprintf('Y-m-%02u', $i), strtotime('first day of last month'))] ?? 0) / GiB, range(1, $lastDays)),
            'currentYear' => array_map(static fn ($i) => $monthlyFlows[date(sprintf('Y-%02u', $i))] ?? 0, range(1, date('m'))),
            'lastYear' => array_map(static fn ($i) => $monthlyFlows[date(sprintf('Y-%02u', $i), strtotime('-1 years'))] ?? 0, $months),
            'yearlyFlows' => $yearlyFlows->toArray(),
            'avgDaily30d' => round(($total30Days / GiB) / $daysWithData, 2),
        ];

        if ($nodeId) {
            $totalAll30d = NodeDailyDataFlow::where('created_at', '>=', $thirtyDaysAgo)->whereNull('node_id')->sum(DB::raw('u + d'));

            $data['nodePct30d'] = round(($total30Days / max($totalAll30d, 1)) * 100, 2);
        }

        $data['currentMonth'][$currentDays - 1] = $todayFlow;
        $data['currentYear'][count($data['currentYear']) - 1] += $todayFlow;

        return view('admin.report.siteDataAnalysis', compact('data', 'nodes'));
    }
}
