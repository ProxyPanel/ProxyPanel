<?php

namespace App\Http\Controllers\Admin;

use App\Components\IP;
use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\NodeOnlineIp;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\PaymentCallback;
use App\Models\User;
use App\Models\UserBanedLog;
use App\Models\UserCreditLog;
use App\Models\UserDataFlowLog;
use App\Models\UserDataModifyLog;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    // 订单列表
    public function orderList(Request $request)
    {
        $email = $request->input('email');
        $order_sn = $request->input('order_sn');
        $is_coupon = $request->input('is_coupon');
        $is_expire = $request->input('is_expire');
        $pay_way = $request->input('pay_way');
        $status = $request->input('status');
        $range_time = $request->input('range_time');
        $sort = $request->input('sort'); // 0-按创建时间降序、1-按创建时间升序
        $order_id = $request->input('id');

        $query = Order::with(['user:id,email', 'goods:id,name', 'coupon:id,name,sn']);

        if (isset($email)) {
            $query->whereHas('user', static function ($q) use ($email) {
                $q->where('email', 'like', '%'.$email.'%');
            });
        }
        if (isset($order_sn)) {
            $query->where('order_sn', 'like', '%'.$order_sn.'%');
        }

        if (isset($is_coupon)) {
            if ($is_coupon) {
                $query->where('coupon_id', '<>', null);
            } else {
                $query->whereCouponId(null);
            }
        }

        if (isset($is_expire)) {
            $query->whereIsExpire($is_expire);
        }

        if (isset($pay_way)) {
            $query->wherePayWay($pay_way);
        }

        if (isset($status)) {
            $query->whereStatus($status);
        }

        if (isset($range_time) && $range_time !== ',') {
            $range_time = explode(',', $range_time);
            $query->where('created_at', '>=', $range_time[0])->where('created_at', '<=', $range_time[1]);
        }

        if (isset($order_id)) {
            $query->whereId($order_id);
        }

        if ($sort) {
            $query->orderBy('id');
        } else {
            $query->orderByDesc('id');
        }

        return view('admin.logs.order', ['orders' => $query->paginate(15)->appends($request->except('page'))]);
    }

    // 流量日志
    public function trafficLog(Request $request)
    {
        $port = $request->input('port');
        $user_id = $request->input('user_id');
        $email = $request->input('email');
        $nodeId = $request->input('nodeId');
        $startTime = $request->input('startTime');
        $endTime = $request->input('endTime');

        $query = UserDataFlowLog::with(['user', 'node']);

        if (isset($port)) {
            $query->whereHas('user', static function ($q) use ($port) {
                $q->wherePort($port);
            });
        }

        if (isset($user_id)) {
            $query->whereUserId($user_id);
        }

        if (isset($email)) {
            $query->whereHas('user', static function ($q) use ($email) {
                $q->where('email', 'like', '%'.$email.'%');
            });
        }

        if (isset($nodeId)) {
            $query->whereNodeId($nodeId);
        }

        if (isset($startTime)) {
            $query->where('log_time', '>=', strtotime($startTime));
        }

        if (isset($endTime)) {
            $query->where('log_time', '<=', strtotime($endTime));
        }

        $dataFlowLogs = $query->latest('log_time')->paginate(20)->appends($request->except('page'));
        foreach ($dataFlowLogs as $log) {
            $log->u = flowAutoShow($log->u);
            $log->d = flowAutoShow($log->d);
            $log->log_time = date('Y-m-d H:i:s', $log->log_time);
        }

        return view('admin.logs.traffic', [
            'totalTraffic' => flowAutoShow($query->sum('u') + $query->sum('d')), // 已使用流量
            'dataFlowLogs' => $dataFlowLogs,
            'nodes' => Node::whereStatus(1)->orderByDesc('sort')->latest()->get(),
        ]);
    }

    // 邮件发送日志列表
    public function notificationLog(Request $request)
    {
        $email = $request->input('email');
        $type = $request->input('type');

        $query = NotificationLog::query();

        if (isset($email)) {
            $query->where('address', 'like', '%'.$email.'%');
        }

        if (isset($type)) {
            $query->whereType($type);
        }

        return view('admin.logs.notification', ['notificationLogs' => $query->latest()->paginate(15)->appends($request->except('page'))]);
    }

    // 在线IP监控（实时）
    public function onlineIPMonitor(Request $request, $id = null)
    {
        $ip = $request->input('ip');
        $email = $request->input('email');
        $port = $request->input('port');
        $nodeId = $request->input('nodeId');

        $query = NodeOnlineIp::with(['node:id,name', 'user:id,email'])->where('created_at', '>=', strtotime('-2 minutes'));

        if (isset($ip)) {
            $query->whereIp($ip);
        }

        if (isset($email)) {
            $query->whereHas('user', static function ($q) use ($email) {
                $q->where('email', 'like', '%'.$email.'%');
            });
        }

        if (isset($port)) {
            $query->whereHas('user', static function ($q) use ($port) {
                $q->wherePort($port);
            });
        }

        if (isset($nodeId)) {
            $query->whereHas('node', static function ($q) use ($nodeId) {
                $q->whereId($nodeId);
            });
        }

        if (isset($id)) {
            $query->whereHas('user', static function ($q) use ($id) {
                $q->whereId($id);
            });
        }

        $onlineIPLogs = $query->groupBy('user_id', 'node_id')->latest()->paginate(20)->appends($request->except('page'));
        foreach ($onlineIPLogs as $log) {
            // 跳过上报多IP的
            if ($log->ip === null || strpos($log->ip, ',') !== false) {
                continue;
            }
            $ipInfo = IP::getIPInfo($log->ip);

            $log->ipInfo = implode(' ', $ipInfo);
        }

        return view('admin.logs.onlineIPMonitor', [
            'onlineIPLogs' => $onlineIPLogs,
            'nodes' => Node::whereStatus(1)->orderByDesc('sort')->latest()->get(),
        ]);
    }

    // 用户余额变动记录
    public function userCreditLogList(Request $request)
    {
        $email = $request->input('email');

        $query = UserCreditLog::with('user:id,email')->latest();

        if (isset($email)) {
            $query->whereHas('user', static function ($q) use ($email) {
                $q->where('email', 'like', '%'.$email.'%');
            });
        }

        return view('admin.logs.userCreditHistory', ['userCreditLogs' => $query->paginate(15)->appends($request->except('page'))]);
    }

    // 用户封禁记录
    public function userBanLogList(Request $request)
    {
        $email = $request->input('email');

        $query = UserBanedLog::with('user:id,email,t')->latest();

        if (isset($email)) {
            $query->whereHas('user', static function ($q) use ($email) {
                $q->where('email', 'like', '%'.$email.'%');
            });
        }

        return view('admin.logs.userBanHistory', ['userBanLogs' => $query->paginate(15)->appends($request->except('page'))]);
    }

    // 用户流量变动记录
    public function userTrafficLogList(Request $request)
    {
        $email = $request->input('email');

        $query = UserDataModifyLog::with(['user:id,email', 'order.goods:id,name']);

        if (isset($email)) {
            $query->whereHas('user', static function ($q) use ($email) {
                $q->where('email', 'like', '%'.$email.'%');
            });
        }

        return view('admin.logs.userTraffic', ['userTrafficLogs' => $query->latest()->paginate(15)->appends($request->except('page'))]);
    }

    // 用户在线IP记录
    public function userOnlineIPList(Request $request)
    {
        $email = $request->input('email');
        $port = $request->input('port');
        $wechat = $request->input('wechat');
        $qq = $request->input('qq');

        $query = User::activeUser();

        if (isset($email)) {
            $query->where('email', 'like', '%'.$email.'%');
        }

        if (isset($wechat)) {
            $query->where('wechat', 'like', '%'.$wechat.'%');
        }

        if (isset($qq)) {
            $query->where('qq', 'like', '%'.$qq.'%');
        }

        if (isset($port)) {
            $query->wherePort($port);
        }

        $userList = $query->paginate(15)->appends($request->except('page'));

        $nodeOnlineIPs = NodeOnlineIp::with('node:id,name')->where('created_at', '>=', strtotime('-10 minutes'))->latest()->distinct()->get();
        foreach ($userList as $user) {
            // 最近5条在线IP记录，如果后端设置为60秒上报一次，则为10分钟内的在线IP
            $user->onlineIPList = $nodeOnlineIPs->where('port', '==', $user->port)->chunk(5);
        }

        return view('admin.logs.userOnlineIP', ['userList' => $userList]);
    }

    // 用户流量监控
    public function userTrafficMonitor(User $user)
    {
        return view('admin.logs.userMonitor', array_merge(['email' => $user->email], $this->dataFlowChart($user->id)));
    }

    // 回调日志
    public function callbackList(Request $request)
    {
        $status = $request->input('status', 0);

        $query = PaymentCallback::query();

        if (isset($status)) {
            $query->whereStatus($status);
        }

        return view('admin.logs.callback', ['callbackLogs' => $query->latest()->paginate(10)->appends($request->except('page'))]);
    }
}
