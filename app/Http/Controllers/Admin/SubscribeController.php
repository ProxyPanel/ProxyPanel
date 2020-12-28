<?php

namespace App\Http\Controllers\Admin;

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
        $user_id = $request->input('user_id');
        $email = $request->input('email');
        $status = $request->input('status');

        $query = UserSubscribe::with(['user:id,email']);

        if (isset($user_id)) {
            $query->whereUserId($user_id);
        }

        if (isset($email)) {
            $query->whereHas('user', static function ($q) use ($email) {
                $q->where('email', 'like', '%'.$email.'%');
            });
        }

        if (isset($status)) {
            $query->whereStatus($status);
        }

        return view('admin.subscribe.index', ['subscribeList' => $query->latest()->paginate(20)->appends($request->except('page'))]);
    }

    //订阅记录
    public function subscribeLog($id)
    {
        $query = UserSubscribeLog::with('user:email');

        if (isset($id)) {
            $query->whereUserSubscribeId($id);
        }

        return view('admin.subscribe.log', ['subscribeLog' => $query->latest()->paginate(20)->appends(\request('page'))]);
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
