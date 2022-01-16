<?php

namespace App\Http\Controllers\Admin;

use App\Components\IP;
use App\Http\Controllers\Controller;
use App\Models\UserSubscribe;
use App\Models\UserSubscribeLog;
use Illuminate\Http\Request;
use Response;

/**
 * 订阅控制器.
 *
 * Class SubscribeController
 */
class SubscribeController extends Controller
{
    // 订阅码列表
    public function index(Request $request)
    {
        $query = UserSubscribe::with(['user:id,username']);

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%{$username}%");
            });
        });

        foreach (['user_id', 'status', 'code'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        return view('admin.subscribe.index', ['subscribeList' => $query->latest()->paginate(20)->appends($request->except('page'))]);
    }

    //订阅记录
    public function subscribeLog($id)
    {
        $query = UserSubscribeLog::with('user:username');

        if (isset($id)) {
            $query->whereUserSubscribeId($id);
        }

        $subscribeLogs = $query->latest()->paginate(20)->appends(\request('page'));
        foreach ($subscribeLogs as $log) {
            // 跳过上报多IP的
            if ($log->request_ip) {
                $log->ipInfo = implode(' ', IP::getIPInfo($log->request_ip));
            }
        }

        return view('admin.subscribe.log', ['subscribeLog' => $subscribeLogs]);
    }

    // 设置用户的订阅的状态
    public function setSubscribeStatus(UserSubscribe $subscribe)
    {
        if ($subscribe->status) {
            $subscribe->update(['status' => 0, 'ban_time' => time(), 'ban_desc' => '后台手动封禁']);
        } else {
            $subscribe->update(['status' => 1, 'ban_time' => null, 'ban_desc' => '']);
        }

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }
}
