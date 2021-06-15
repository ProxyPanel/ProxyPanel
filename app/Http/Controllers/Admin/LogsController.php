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
        $query = Order::with(['user:id,email', 'goods:id,name', 'coupon:id,name,sn']);

        $request->whenFilled('email', function ($email) use ($query) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', 'like', "%{$email}%");
            });
        });

        $request->whenFilled('sn', function ($value) use ($query) {
            $query->where('sn', 'like', "%{$value}%");
        });

        $request->whenFilled('start', function ($value) use ($query) {
            $query->where('created_at', '>=', $value);
        });

        $request->whenFilled('end', function ($value) use ($query) {
            $query->where('created_at', '<=', $value);
        });

        $request->whenFilled('is_coupon', function ($value) use ($query) {
            if ($value) {
                $query->where('coupon_id', '<>');
            } else {
                $query->where('coupon_id');
            }
        });

        foreach (['is_expire', 'pay_way', 'status'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        // 0-按创建时间降序、1-按创建时间升序 默认为按创建时间降序
        if ($request->filled('sort') && $request->input('sort') === '1') {
            $query->oldest();
        } else {
            $query->latest();
        }

        return view('admin.logs.order', ['orders' => $query->paginate(15)->appends($request->except('page'))]);
    }

    // 流量日志
    public function trafficLog(Request $request)
    {
        $query = UserDataFlowLog::with(['user', 'node']);

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

        $request->whenFilled('email', function ($email) use ($query) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', 'like', "%{$email}%");
            });
        });

        $request->whenFilled('start', function ($value) use ($query) {
            $query->where('log_time', '>=', strtotime($value));
        });

        $request->whenFilled('end', function ($value) use ($query) {
            $query->where('log_time', '<=', strtotime($value));
        });

        $totalTraffic = flowAutoShow($query->sum('u') + $query->sum('d')); // 在分页前，计算总使用流量
        $dataFlowLogs = $query->latest('log_time')->paginate(20)->appends($request->except('page'));
        foreach ($dataFlowLogs as $log) {
            $log->u = flowAutoShow($log->u);
            $log->d = flowAutoShow($log->d);
            $log->log_time = date('Y-m-d H:i:s', $log->log_time);
        }
        $nodes = Node::whereStatus(1)->orderByDesc('sort')->latest()->get();

        return view('admin.logs.traffic', compact(['totalTraffic', 'dataFlowLogs', 'nodes']));
    }

    // 邮件发送日志列表
    public function notificationLog(Request $request)
    {
        $query = NotificationLog::query();

        $request->whenFilled('email', function ($email) use ($query) {
            $query->where('address', 'like', "%{$email}%");
        });

        $request->whenFilled('type', function ($type) use ($query) {
            $query->whereType($type);
        });

        return view('admin.logs.notification', ['notificationLogs' => $query->latest()->paginate(15)->appends($request->except('page'))]);
    }

    // 在线IP监控（实时）
    public function onlineIPMonitor(Request $request, $id = null)
    {
        $query = NodeOnlineIp::with(['node:id,name', 'user:id,email'])->where('created_at', '>=', strtotime('-2 minutes'));

        if ($id !== null) {
            $query->whereHas('user', static function ($query) use ($id) {
                $query->whereId($id);
            });
        }

        $request->whenFilled('ip', function ($ip) use ($query) {
            $query->whereIp($ip);
        });

        $request->whenFilled('email', function ($email) use ($query) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', 'like', "%{$email}%");
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
            'nodes'        => Node::whereStatus(1)->orderByDesc('sort')->latest()->get(),
        ]);
    }

    // 用户余额变动记录
    public function userCreditLogList(Request $request)
    {
        $query = UserCreditLog::with('user:id,email')->latest();

        $request->whenFilled('email', function ($value) use ($query) {
            $query->whereHas('user', function ($query) use ($value) {
                $query->where('email', 'like', "%{$value}%");
            });
        });

        return view('admin.logs.userCreditHistory', ['userCreditLogs' => $query->paginate(15)->appends($request->except('page'))]);
    }

    // 用户封禁记录
    public function userBanLogList(Request $request)
    {
        $query = UserBanedLog::with('user:id,email,t');

        $request->whenFilled('email', function ($value) use ($query) {
            $query->whereHas('user', function ($query) use ($value) {
                $query->where('email', 'like', "%{$value}%");
            });
        });

        return view('admin.logs.userBanHistory', ['userBanLogs' => $query->latest()->paginate(15)->appends($request->except('page'))]);
    }

    // 用户流量变动记录
    public function userTrafficLogList(Request $request)
    {
        $query = UserDataModifyLog::with(['user:id,email', 'order.goods:id,name']);

        $request->whenFilled('email', function ($value) use ($query) {
            $query->whereHas('user', function ($query) use ($value) {
                $query->where('email', 'like', "%{$value}%");
            });
        });

        return view('admin.logs.userTraffic', ['userTrafficLogs' => $query->latest()->paginate(15)->appends($request->except('page'))]);
    }

    // 用户在线IP记录
    public function userOnlineIPList(Request $request)
    {
        $query = User::activeUser();

        foreach (['email', 'wechat', 'qq'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, 'like', "%{$value}%");
            });
        }

        foreach (['id', 'port'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        $userList = $query->orderBy('id')->paginate(15)->appends($request->except('page'));

        $nodeOnlineIPs = NodeOnlineIp::with('node:id,name')->where('created_at', '>=', strtotime('-10 minutes'))->latest()->distinct()->get();
        foreach ($userList as $user) {
            //Todo node_online_ip表 api可以用user_id
            // 最近5条在线IP记录，如果后端设置为60秒上报一次，则为10分钟内的在线IP
            $user->onlineIPList = $nodeOnlineIPs->where('port', $user->port)->take(5);
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
        $query = PaymentCallback::query();

        foreach (['trade_no', 'out_trade_no', 'status'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        return view('admin.logs.callback', ['callbackLogs' => $query->latest()->paginate(10)->appends($request->except('page'))]);
    }
}
