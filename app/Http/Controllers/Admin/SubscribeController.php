<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubscribe;
use App\Utils\IP;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscribeController extends Controller
{
    public function index(Request $request): View
    { // 订阅码列表
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

    public function subscribeLog(Request $request, UserSubscribe $userSubscribe): View
    { // 订阅记录
        $query = $userSubscribe->userSubscribeLogs();

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

        // 批量获取 IP 信息以减少查询次数
        $ipList = $subscribeLogs->pluck('request_ip')->filter()->unique()->toArray();
        $ipInfoMap = [];

        foreach ($ipList as $ip) {
            if ($ip) {
                $ipInfo = IP::getIPInfo($ip);
                $ipInfoMap[$ip] = $ipInfo ? ($ipInfo['address'] ?? null) : null;
            }
        }

        // 将 IP 信息附加到日志记录中
        $subscribeLogs->getCollection()->transform(function ($log) use ($ipInfoMap) {
            $log->ipInfo = $log->request_ip ? ($ipInfoMap[$log->request_ip] ?? null) : null;

            return $log;
        });

        return view('admin.subscribe.log', ['subscribeLog' => $subscribeLogs, 'subscribe' => $userSubscribe]);
    }

    public function setSubscribeStatus(UserSubscribe $userSubscribe): JsonResponse
    {
        $data = $userSubscribe->status
            ? ['status' => 0, 'ban_time' => strtotime(sysConfig('ban_duration').' minutes'), 'ban_desc' => 'Your subscription has been disabled by the administrator, please contact the administrator to restore it']
            : ['status' => 1, 'ban_time' => null, 'ban_desc' => null];

        $ret = $userSubscribe->update($data)
            ? ['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.update')])]
            : ['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.update')])];

        return response()->json($ret);
    }
}
