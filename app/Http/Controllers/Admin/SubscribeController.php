<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubscribe;
use App\Models\UserSubscribeLog;
use App\Utils\IP;
use Illuminate\Http\JsonResponse;
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
                $query->where('username', 'like', "%$username%");
            });
        });

        foreach (['user_id', 'status', 'code'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        return view('admin.subscribe.index', ['subscribeList' => $query->sortable(['id' => 'desc'])->paginate(20)->appends($request->except('page'))]);
    }

    //订阅记录
    public function subscribeLog(Request $request, $userSubscribeId)
    {
        $query = UserSubscribeLog::whereUserSubscribeId($userSubscribeId);

        $request->whenFilled('id', function ($value) use ($query) {
            $query->where('id', $value);
        });

        $request->whenFilled('ip', function ($value) use ($query) {
            $query->where('request_ip', 'like', "%$value%");
        });

        if ($request->filled('start')) {
            $query->whereBetween('request_time', [$request->input('start').' 00:00:00', $request->input('end').' 23:59:59']);
        }

        $subscribeLogs = $query->latest()->paginate(20)->appends($request->except('page'));
        foreach ($subscribeLogs as $log) {
            // 跳过上报多IP的
            if ($log->request_ip) {
                $log->ipInfo = IP::getIPInfo($log->request_ip)['address'] ?? null;
            }
        }

        return view('admin.subscribe.log', ['subscribeLog' => $subscribeLogs, 'subscribe' => UserSubscribe::find($userSubscribeId)]);
    }

    // 设置用户的订阅的状态
    public function setSubscribeStatus(UserSubscribe $subscribe): JsonResponse
    {
        if ($subscribe->status) {
            $subscribe->update(['status' => 0, 'ban_time' => strtotime(sysConfig('traffic_ban_time').' minutes'), 'ban_desc' => 'Your subscription has been disabled by the administrator, please contact the administrator to restore it']);
        } else {
            $subscribe->update(['status' => 1, 'ban_time' => null, 'ban_desc' => null]);
        }

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }
}
