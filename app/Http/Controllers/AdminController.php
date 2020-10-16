<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Models\Country;
use App\Models\Invite;
use App\Models\Label;
use App\Models\Level;
use App\Models\Node;
use App\Models\NodeDailyDataFlow;
use App\Models\Order;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use App\Models\SsConfig;
use App\Models\User;
use App\Models\UserHourlyDataFlow;
use Auth;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Redirect;
use Response;
use Str;

/**
 * 管理员控制器.
 *
 * Class AdminController
 */
class AdminController extends Controller
{
    public function index()
    {
        $past = strtotime('-'.sysConfig('expire_days').' days');

        $view['expireDays'] = sysConfig('expire_days');
        $view['totalUserCount'] = User::count(); // 总用户数
        $view['todayRegister'] = User::whereDate('created_at', date('Y-m-d'))->count(); // 今日注册用户
        $view['enableUserCount'] = User::whereEnable(1)->count(); // 有效用户数
        $view['activeUserCount'] = User::where('t', '>=', $past)->count(); // 活跃用户数
        $view['unActiveUserCount'] = User::whereEnable(1)->whereBetween('t', [1, $past])->count(); // 不活跃用户数
        $view['onlineUserCount'] = User::where('t', '>=', strtotime('-10 minutes'))->count(); // 10分钟内在线用户数
        $view['expireWarningUserCount'] = User::whereBetween(
            'expired_at',
            [date('Y-m-d'), date('Y-m-d', strtotime('+'.sysConfig('expire_days').' days'))]
        )->count(); // 临近过期用户数
        $view['largeTrafficUserCount'] = User::whereRaw('(u + d) >= 107374182400')->where('status', '<>', -1)->count(); // 流量超过100G的用户
        $view['flowAbnormalUserCount'] = count((new UserHourlyDataFlow())->trafficAbnormal()); // 1小时内流量异常用户
        $view['nodeCount'] = Node::count();
        $view['unnormalNodeCount'] = Node::whereStatus(0)->count();
        $view['flowCount'] = flowAutoShow(NodeDailyDataFlow::where('created_at', '>=', date('Y-m-d', strtotime('-30 days')))->sum('total'));
        $view['todayFlowCount'] = flowAutoShow(NodeDailyDataFlow::where('created_at', '>=', date('Y-m-d'))->sum('total'));
        $view['totalFlowCount'] = flowAutoShow(NodeDailyDataFlow::sum('total'));
        $view['totalCredit'] = User::where('credit', '<>', 0)->sum('credit') / 100;
        $view['totalWaitRefAmount'] = ReferralLog::whereIn('status', [0, 1])->sum('commission') / 100;
        $view['todayWaitRefAmount'] = ReferralLog::whereIn('status', [0, 1])->whereDate('created_at', date('Y-m-d'))->sum('commission') / 100;
        $view['totalRefAmount'] = ReferralApply::whereStatus(2)->sum('amount') / 100;
        $view['totalOrder'] = Order::count();
        $view['todayOrder'] = Order::whereDate('created_at', date('Y-m-d'))->count();
        $view['totalOnlinePayOrder'] = Order::where('pay_type', '<>', 0)->count();
        $view['todayOnlinePayOrder'] = Order::where('pay_type', '<>', 0)->whereDate('created_at', date('Y-m-d'))->count();
        $view['totalSuccessOrder'] = Order::whereStatus(2)->count();
        $view['todaySuccessOrder'] = Order::whereStatus(2)->whereDate('created_at', date('Y-m-d'))->count();

        return view('admin.index', $view);
    }

    // 修改个人资料
    public function profile(Request $request)
    {
        if ($request->isMethod('POST')) {
            $new_password = $request->input('new_password');

            if (!Hash::check($request->input('old_password'), Auth::getUser()->password)) {
                return Redirect::back()->withErrors('旧密码错误，请重新输入');
            }

            if (Hash::check($new_password, Auth::getUser()->password)) {
                return Redirect::back()->withErrors('新密码不可与旧密码一样，请重新输入');
            }

            $ret = Auth::getUser()->update(['password' => $new_password]);
            if (!$ret) {
                return Redirect::back()->withErrors('修改失败');
            }

            return Redirect::back()->with('successMsg', '修改成功');
        }

        return view('admin.config.profile');
    }

    // 邀请码列表
    public function inviteList(Request $request)
    {
        $view['inviteList'] = Invite::with(['invitee:id,email', 'inviter:id,email'])
            ->orderBy('status')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->except('page'));

        return view('admin.inviteList', $view);
    }

    // 生成邀请码
    public function makeInvite(): JsonResponse
    {
        for ($i = 0; $i < 10; $i++) {
            $obj = new Invite();
            $obj->inviter_id = 0;
            $obj->invitee_id = 0;
            $obj->code = strtoupper(substr(md5(microtime().Str::random(6)), 8, 12));
            $obj->status = 0;
            $obj->dateline = date('Y-m-d H:i:s', strtotime('+'.sysConfig('admin_invite_days').' days'));
            $obj->save();
        }

        return Response::json(['status' => 'success', 'message' => '生成成功']);
    }

    // 导出邀请码
    public function exportInvite()
    {
        $inviteList = Invite::whereStatus(0)->orderBy('id')->get();
        $filename = '邀请码'.date('Ymd').'.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('ProxyPanel')
            ->setLastModifiedBy('ProxyPanel')
            ->setTitle('邀请码')
            ->setSubject('邀请码');

        try {
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('邀请码');
            $sheet->fromArray(['邀请码', '有效期'], null);

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
        $view['methodList'] = SsConfig::type(1)->get();
        $view['protocolList'] = SsConfig::type(2)->get();
        $view['obfsList'] = SsConfig::type(3)->get();
        $view['countryList'] = Country::all();
        $view['levelList'] = Level::all();
        $view['labelList'] = Label::with('nodes')->get();

        return view('admin.config.config', $view);
    }

    public function getPort(): int
    {
        return Helpers::getPort();
    }
}
