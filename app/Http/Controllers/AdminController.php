<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\NodeDailyDataFlow;
use App\Models\NodeHourlyDataFlow;
use App\Models\Order;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use App\Models\User;
use App\Models\UserHourlyDataFlow;
use DB;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $past = strtotime('-'.sysConfig('expire_days').' days');
        $today = today();

        $stats = cache()->remember('user_stats', now()->addMinutes(5), function () use ($today) {
            $dailyTrafficUsage = NodeHourlyDataFlow::whereDate('created_at', $today)->sum(DB::raw('u + d'));

            return [
                'monthlyTrafficUsage' => formatBytes(NodeDailyDataFlow::whereNull('node_id')->whereMonth('created_at', now()->month)->sum(DB::raw('u + d'))),
                'dailyTrafficUsage' => $dailyTrafficUsage ? formatBytes($dailyTrafficUsage) : 0,
                'totalTrafficUsage' => formatBytes(NodeDailyDataFlow::whereNull('node_id')->where('created_at', '>=', now()->subDays(30))->sum(DB::raw('u + d'))),
            ];
        });

        return view('admin.index', [
            'totalUserCount' => User::count(), // 总用户数
            'todayRegister' => User::whereDate('created_at', $today)->count(), // 今日注册用户
            'enableUserCount' => User::whereEnable(1)->count(), // 有效用户数
            'activeUserCount' => User::where('t', '>=', $past)->count(), // 活跃用户数
            'payingUserCount' => User::has('paidOrders')->count(), // 付费用户数
            'payingNewUserCount' => User::whereDate('created_at', $today)->has('paidOrders')->count(), // 今日新增付费用户
            'inactiveUserCount' => User::whereEnable(1)->where('t', '<', $past)->count(), // 不活跃用户数
            'onlineUserCount' => User::where('t', '>=', strtotime('-10 minutes'))->count(), // 10分钟内在线用户数
            'expireWarningUserCount' => User::whereBetween('expired_at', [$today, today()->addDays(sysConfig('expire_days'))])->count(), // 临近过期用户数
            'largeTrafficUserCount' => User::whereRaw('(u + d)/transfer_enable >= 0.9')->where('status', '<>', -1)->count(), // 流量使用超过90%的用户
            'flowAbnormalUserCount' => count((new UserHourlyDataFlow)->trafficAbnormal()), // 1小时内流量异常用户
            'nodeCount' => Node::count(),
            'abnormalNodeCount' => Node::whereStatus(0)->count(),
            'monthlyTrafficUsage' => $stats['monthlyTrafficUsage'],
            'dailyTrafficUsage' => $stats['dailyTrafficUsage'],
            'totalTrafficUsage' => $stats['totalTrafficUsage'],
            'totalCredit' => User::where('credit', '<>', 0)->sum('credit') / 100,
            'totalWaitRefAmount' => ReferralLog::whereIn('status', [0, 1])->sum('commission') / 100,
            'todayWaitRefAmount' => ReferralLog::whereIn('status', [0, 1])->whereDate('created_at', $today)->sum('commission') / 100,
            'totalRefAmount' => ReferralApply::whereStatus(2)->sum('amount') / 100,
            'totalOrder' => Order::count(),
            'todayOrder' => Order::whereDate('created_at', $today)->count(),
            'totalOnlinePayOrder' => Order::where('pay_type', '<>', 0)->count(),
            'todayOnlinePayOrder' => Order::where('pay_type', '<>', 0)->whereDate('created_at', $today)->count(),
            'totalSuccessOrder' => Order::whereIn('status', [2, 3])->count(),
            'todaySuccessOrder' => Order::whereIn('status', [2, 3])->whereDate('created_at', $today)->count(),
        ]);
    }
}
