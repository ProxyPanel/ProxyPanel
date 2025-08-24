<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DataChart;
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
use App\Utils\IP;
use App\Utils\Payments\PaymentManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    use DataChart;

    public function orderList(Request $request): View
    { // 订单列表
        $query = Order::with(['user:id,username', 'goods:id,name', 'coupon:id,name,sn']);

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        $request->whenFilled('sn', function ($value) use ($query) {
            $query->where('sn', 'like', "%$value%");
        });

        $request->whenFilled('start', function ($value) use ($query) {
            $query->where('created_at', '>=', $value);
        });

        $request->whenFilled('end', function ($value) use ($query) {
            $query->where('created_at', '<=', $value.' 23:59:59');
        });

        $request->whenFilled('is_coupon', function ($value) use ($query) {
            if ($value) {
                $query->where('coupon_id', '<>', null);
            } else {
                $query->where('coupon_id', null);
            }
        });

        foreach (['is_expire', 'pay_way', 'status'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            });
        }

        return view('admin.logs.order', ['orders' => $query->sortable(['id' => 'desc'])->paginate(15)->appends($request->except('page')), 'paymentLabels' => PaymentManager::getLabels(true)]);
    }

    public function changeOrderStatus(Request $request): JsonResponse
    {
        $order = Order::findOrFail($request->input('oid'));
        $status = (int) $request->input('status');

        if ($order->status === 3 && $status === 2 && $order->goods->type === 2 && Order::userActivePlan($order->user_id)->exists()) {
            return response()->json(['status' => 'fail', 'message' => trans('admin.logs.order.update_conflict')]); // 防止预支付订单假激活
        }

        if ($order->update(['is_expire' => 0, 'expired_at' => null, 'status' => $status])) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.update')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.update')])]);
    }

    public function trafficLog(Request $request): View
    { // 流量日志
        $query = UserDataFlowLog::with(['user:id,username,port', 'node:id,name']);

        $request->whenFilled('port', function ($value) use ($query) {
            $query->whereHas('user', function ($query) use ($value) {
                $query->wherePort($value);
            });
        });

        foreach (['user_id', 'node_id'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        $request->whenFilled('start', function ($value) use ($query) {
            $query->where('log_time', '>=', strtotime($value));
        });

        $request->whenFilled('end', function ($value) use ($query) {
            $query->where('log_time', '<=', strtotime($value) + 86399);
        });

        $totalTraffic = formatBytes($query->sum('u') + $query->sum('d')); // 在分页前，计算总使用流量
        $dataFlowLogs = $query->latest('log_time')->paginate(20)->appends($request->except('page'));
        foreach ($dataFlowLogs as $log) {
            $log->u = formatBytes($log->u);
            $log->d = formatBytes($log->d);
            $log->log_time = date('Y-m-d H:i:s', $log->log_time);
        }
        $nodes = Node::whereStatus(1)->orderByDesc('sort')->latest()->pluck('name', 'id');

        return view('admin.logs.traffic', compact(['totalTraffic', 'dataFlowLogs', 'nodes']));
    }

    public function notificationLog(Request $request): View
    { // 邮件发送日志列表
        $query = NotificationLog::query();

        $request->whenFilled('username', function ($username) use ($query) {
            $query->where('address', 'like', "%$username%");
        });

        $request->whenFilled('type', function ($type) use ($query) {
            $query->whereType($type);
        });

        return view('admin.logs.notification', ['notificationLogs' => $query->latest()->paginate(15)->appends($request->except('page'))]);
    }

    public function onlineIPMonitor(Request $request, ?int $id = null): View
    { // 在线IP监控（实时）
        $query = NodeOnlineIp::with(['node:id,name', 'user:id,username,port'])->where('created_at', '>=', strtotime('-2 minutes'));

        if ($id !== null) {
            $query->whereHas('user', static function ($query) use ($id) {
                $query->whereId($id);
            });
        }

        $request->whenFilled('ip', function ($ip) use ($query) {
            $query->whereIp($ip);
        });

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        $request->whenFilled('port', function ($port) use ($query) {
            $query->whereHas('user', function ($query) use ($port) {
                $query->wherePort($port);
            });
        });

        $request->whenFilled('node_id', function ($nodeId) use ($query) {
            $query->whereHas('node', function ($query) use ($nodeId) {
                $query->whereId($nodeId);
            });
        });

        $onlineIPLogs = $query->groupBy(['user_id', 'node_id'])->latest()->paginate(20)->appends($request->except('page'))->through(function ($log) {
            // 提前过滤 IP 数据以减少循环中的判断
            if ($log->ip && ! str_contains($log->ip, ',') && $info = IP::getIPInfo($log->ip)) {
                $log->ipInfo = $info['address'].' '.$info['isp'];
            }

            return $log;
        });

        return view('admin.logs.onlineIPMonitor', [
            'onlineIPLogs' => $onlineIPLogs,
            'nodes' => Node::whereStatus(1)->orderByDesc('sort')->latest()->pluck('name', 'id'),
        ]);
    }

    public function userCreditLogList(Request $request): View
    { // 用户余额变动记录
        $query = UserCreditLog::with('user:id,username')->latest();

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        return view('admin.logs.userCreditHistory', ['userCreditLogs' => $query->paginate(15)->appends($request->except('page'))]);
    }

    public function userBanLogList(Request $request): View
    { // 用户封禁记录
        $query = UserBanedLog::with('user:id,username,t');

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        return view('admin.logs.userBanHistory', ['userBanLogs' => $query->latest()->paginate(15)->appends($request->except('page'))]);
    }

    public function userTrafficLogList(Request $request): View
    { // 用户流量变动记录
        $query = UserDataModifyLog::with(['user:id,username', 'order.goods:id,name']);

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        return view('admin.logs.userTraffic', ['userTrafficLogs' => $query->latest()->paginate(15)->appends($request->except('page'))]);
    }

    public function userOnlineIPList(Request $request): View
    { // 用户在线IP记录
        $query = User::activeUser();

        foreach (['username', 'wechat', 'qq'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, 'like', "%$value%");
            });
        }

        foreach (['id', 'port'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        $userList = $query->orderBy('id')->paginate(15)->appends($request->except('page'));

        // 获取最近10分钟的在线IP记录
        $nodeOnlineIPs = NodeOnlineIp::with('node:id,name')->where('created_at', '>=', strtotime('-10 minutes'))->latest()->distinct()->get(['user_id', 'node_id', 'port', 'ip', 'type', 'created_at']);

        foreach ($userList as $user) {
            // 最近5条在线IP记录，如果后端设置为60秒上报一次，则为10分钟内的在线IP
            $user->onlineIPList = $nodeOnlineIPs->where('port', $user->port)->take(5);
        }

        return view('admin.logs.userOnlineIP', ['userList' => $userList]);
    }

    public function userTrafficMonitor(User $user): View
    { // 用户流量监控
        return view('admin.logs.userMonitor', ['username' => $user->username, ...$this->dataFlowChart($user->id)]);
    }

    public function callbackList(Request $request): View
    { // 回调日志
        $query = PaymentCallback::query();

        foreach (['trade_no', 'out_trade_no', 'status'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        return view('admin.logs.callback', ['callbackLogs' => $query->latest()->paginate(10)->appends($request->except('page'))]);
    }
}
