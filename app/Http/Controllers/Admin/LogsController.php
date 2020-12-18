<?php

namespace App\Http\Controllers\Admin;

use App\Components\IP;
use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\NodeOnlineUserIp;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\PaymentCallback;
use App\Models\User;
use App\Models\UserBanedLog;
use App\Models\UserCreditLog;
use App\Models\UserDataFlowLog;
use App\Models\UserDataModifyLog;
use Illuminate\Http\Request;
use Redirect;

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

        $view['orderList'] = $query->paginate(15)->appends($request->except('page'));

        return view('admin.logs.order', $view);
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

        // 已使用流量
        $view['totalTraffic'] = flowAutoShow($query->sum('u') + $query->sum('d'));

        $list = $query->latest('log_time')->paginate(20)->appends($request->except('page'));
        foreach ($list as $vo) {
            $vo->u = flowAutoShow($vo->u);
            $vo->d = flowAutoShow($vo->d);
            $vo->log_time = date('Y-m-d H:i:s', $vo->log_time);
        }

        $view['list'] = $list;
        $view['nodeList'] = Node::whereStatus(1)->orderByDesc('sort')->latest()->get();

        return view('admin.logs.traffic', $view);
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

        $view['list'] = $query->latest()->paginate(15)->appends($request->except('page'));

        return view('admin.logs.notification', $view);
    }

    // 在线IP监控（实时）
    public function onlineIPMonitor(Request $request, $id = null)
    {
        $ip = $request->input('ip');
        $email = $request->input('email');
        $port = $request->input('port');
        $nodeId = $request->input('nodeId');

        $query = NodeOnlineUserIp::with(['node:id,name', 'user:id,email'])->where('created_at', '>=', strtotime('-2 minutes'));

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

        $view['list'] = $onlineIPLogs;
        $view['nodeList'] = Node::whereStatus(1)->orderByDesc('sort')->latest()->get();

        return view('admin.logs.onlineIPMonitor', $view);
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

        $view['list'] = $query->paginate(15)->appends($request->except('page'));

        return view('admin.logs.userCreditHistory', $view);
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

        $view['list'] = $query->paginate(15)->appends($request->except('page'));

        return view('admin.logs.userBanHistory', $view);
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

        $view['list'] = $query->latest()->paginate(15)->appends($request->except('page'));

        return view('admin.logs.userTraffic', $view);
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

        $nodeOnlineIPs = NodeOnlineUserIp::with('node:id,name')->where('created_at', '>=', strtotime('-10 minutes'))->latest()->distinct();
        // Todo 优化查询
        foreach ($userList as $user) {
            // 最近5条在线IP记录，如果后端设置为60秒上报一次，则为10分钟内的在线IP
            $user->onlineIPList = $nodeOnlineIPs->wherePort($user->port)->limit(5)->get();
        }

        $view['userList'] = $userList;

        return view('admin.logs.userOnlineIP', $view);
    }

    // 用户流量监控
    public function userTrafficMonitor($id)
    {
        if (empty($id)) {
            return Redirect::back();
        }

        $user = User::find($id);
        if (empty($user)) {
            return Redirect::back();
        }

        $view['email'] = $user->email;
        $view = array_merge($view, $this->dataFlowChart($user->id));

        return view('admin.logs.userMonitor', $view);
    }

    // 回调日志
    public function callbackList(Request $request)
    {
        $status = $request->input('status', 0);

        $query = PaymentCallback::query();

        if (isset($status)) {
            $query->whereStatus($status);
        }

        $view['list'] = $query->latest()->paginate(10)->appends($request->except('page'));

        return view('admin.logs.callback', $view);
    }
}
