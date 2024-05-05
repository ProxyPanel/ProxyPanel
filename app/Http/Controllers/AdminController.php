<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\GoodsCategory;
use App\Models\Invite;
use App\Models\Label;
use App\Models\Level;
use App\Models\Node;
use App\Models\NodeDailyDataFlow;
use App\Models\NodeHourlyDataFlow;
use App\Models\Order;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use App\Models\SsConfig;
use App\Models\User;
use App\Models\UserHourlyDataFlow;
use Cache;
use DB;
use Illuminate\Http\JsonResponse;
use Log;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Response;
use Str;

class AdminController extends Controller
{
    public function index()
    {
        $past = strtotime('-'.sysConfig('expire_days').' days');
        $today = today();

        $stats = Cache::remember('user_stats', now()->addMinutes(5), function () use ($today, $past) {
            $dailyTrafficUsage = NodeHourlyDataFlow::whereDate('created_at', $today)->sum(DB::raw('u + d'));

            return [
                'activeUserCount' => User::where('t', '>=', $past)->count(), // 活跃用户数
                'inactiveUserCount' => User::whereEnable(1)->where('t', '<', $past)->count(), // 不活跃用户数
                'expireWarningUserCount' => User::whereBetween('expired_at', [$today, today()->addDays(sysConfig('expire_days'))])->count(), // 临近过期用户数
                'largeTrafficUserCount' => User::whereRaw('(u + d)/transfer_enable >= 0.9')->where('status', '<>', -1)->count(), // 流量使用超过90%的用户
                'flowAbnormalUserCount' => count((new UserHourlyDataFlow)->trafficAbnormal()), // 1小时内流量异常用户
                'monthlyTrafficUsage' => formatBytes(NodeDailyDataFlow::whereMonth('created_at', now()->month)->sum(DB::raw('u + d'))),
                'dailyTrafficUsage' => $dailyTrafficUsage ? formatBytes($dailyTrafficUsage) : 0,
                'totalTrafficUsage' => formatBytes(NodeDailyDataFlow::sum(DB::raw('u + d'))),
            ];
        });

        return view('admin.index', [
            'totalUserCount' => User::count(), // 总用户数
            'todayRegister' => User::whereDate('created_at', $today)->count(), // 今日注册用户
            'enableUserCount' => User::whereEnable(1)->count(), // 有效用户数
            'activeUserCount' => $stats['activeUserCount'],
            'payingUserCount' => User::has('paidOrders')->count(), // 付费用户数
            'payingNewUserCount' => User::whereDate('created_at', $today)->has('paidOrders')->count(), // 不活跃用户数
            'inactiveUserCount' => $stats['inactiveUserCount'],
            'onlineUserCount' => User::where('t', '>=', strtotime('-10 minutes'))->count(), // 10分钟内在线用户数,
            'expireWarningUserCount' => $stats['expireWarningUserCount'],
            'largeTrafficUserCount' => $stats['largeTrafficUserCount'],
            'flowAbnormalUserCount' => $stats['flowAbnormalUserCount'],
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

    // 邀请码列表
    public function inviteList()
    {
        return view('admin.aff.invite', [
            'inviteList' => Invite::with(['invitee:id,username', 'inviter:id,username'])->orderBy('status')->orderByDesc('id')->paginate(15)->appends(request('page')),
        ]);
    }

    // 生成邀请码
    public function makeInvite(): JsonResponse
    {
        for ($i = 0; $i < 10; $i++) {
            $obj = new Invite();
            $obj->code = strtoupper(substr(md5(microtime().Str::random(6)), 8, 12));
            $obj->dateline = date('Y-m-d H:i:s', strtotime(sysConfig('admin_invite_days').' days'));
            $obj->save();
        }

        return Response::json(['status' => 'success', 'message' => trans('common.generate_item', ['attribute' => trans('common.success')])]);
    }

    // 导出邀请码
    public function exportInvite(): void
    {
        $inviteList = Invite::whereStatus(0)->orderBy('id')->get();
        $filename = '邀请码'.date('Ymd').'.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('ProxyPanel')->setLastModifiedBy('ProxyPanel')->setTitle('邀请码')->setSubject('邀请码');

        try {
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('邀请码');
            $sheet->fromArray(['邀请码', '有效期']);

            foreach ($inviteList as $k => $vo) {
                $sheet->fromArray([$vo->code, $vo->dateline], null, 'A'.($k + 2));
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // 输出07Excel文件
            //header('Content-Type:application/vnd.ms-excel'); // 输出Excel03版本文件
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        } catch (Exception $e) {
            Log::error('导出邀请码时报错：'.$e->getMessage());
        }
    }

    public function config()
    {
        return view('admin.config.common', [
            'methods' => SsConfig::type(1)->get(),
            'protocols' => SsConfig::type(2)->get(),
            'categories' => GoodsCategory::all(),
            'obfsList' => SsConfig::type(3)->get(),
            'countries' => Country::all(),
            'levels' => Level::all(),
            'labels' => Label::with('nodes')->get(),
        ]);
    }
}
