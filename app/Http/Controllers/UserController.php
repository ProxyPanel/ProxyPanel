<?php

namespace App\Http\Controllers;

use App\Helpers\DataChart;
use App\Models\Article;
use App\Services\UserService;
use App\Utils\Helpers;
use Cache;
use DB;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use Redirect;
use Response;
use Session;
use Str;

class UserController extends Controller
{
    use DataChart;

    public function index()
    {
        // 用户转换
        if (Session::has('user')) {
            auth()->loginUsingId(Session::pull('user'));
        }
        $user = auth()->user();
        $totalTransfer = $user->transfer_enable;
        $usedTransfer = $user->used_traffic;
        $unusedTraffic = max($totalTransfer - $usedTransfer, 0);

        $nodes = $user->nodes()->get();
        $subType = [];
        if ($nodes->whereIn('type', [1, 4])->isNotEmpty()) {
            $subType[] = 'ss';
        }
        if ($nodes->where('type', 2)->isNotEmpty()) {
            $subType[] = 'v2';
        }
        if ($nodes->where('type', 3)->isNotEmpty()) {
            $subType[] = 'trojan';
        }

        return view('user.index', array_merge([
            'remainDays' => now()->diffInDays($user->expired_at, false),
            'resetDays' => $user->reset_time ? now()->diffInDays($user->reset_time, false) : null,
            'unusedTraffic' => $unusedTraffic,
            'expireTime' => $user->expiration_date,
            'banedTime' => $user->ban_time,
            'unusedPercent' => $totalTransfer > 0 ? round($unusedTraffic / $totalTransfer, 2) * 100 : 0,
            'announcements' => Article::type(2)->lang()->latest()->simplePaginate(1), // 公告
            'isTrafficWarning' => $user->isTrafficWarning(), // 流量异常判断
            'paying_user' => (new UserService)->isActivePaying(), // 付费用户判断
            'userLoginLog' => $user->loginLogs()->latest()->first(), // 近期登录日志
            'subscribe_status' => $user->subscribe->status,
            'subMsg' => $user->subscribe->ban_desc,
            'subType' => $subType,
            'subUrl' => $user->subUrl(),
        ], $this->dataFlowChart($user->id)));
    }

    // 签到
    public function checkIn(): JsonResponse
    {
        $user = auth()->user();
        // 系统开启登录加积分功能才可以签到
        if (! sysConfig('is_checkin')) {
            return Response::json(['status' => 'fail', 'title' => trans('common.failed'), 'message' => trans('user.home.attendance.disable')]);
        }

        // 已签到过，验证是否有效
        if (Cache::has('userCheckIn_'.$user->id)) {
            return Response::json(['status' => 'success', 'title' => trans('common.success'), 'message' => trans('user.home.attendance.done')]);
        }

        $traffic = random_int((int) sysConfig('min_rand_traffic'), (int) sysConfig('max_rand_traffic')) * MiB;

        if (! $user->incrementData($traffic)) {
            return Response::json(['status' => 'fail', 'title' => trans('common.failed'), 'message' => trans('user.home.attendance.failed')]);
        }
        Helpers::addUserTrafficModifyLog($user->id, $user->transfer_enable, $user->transfer_enable + $traffic, trans('user.home.attendance.attribute'));

        // 多久后可以再签到
        $ttl = sysConfig('traffic_limit_time') ? sysConfig('traffic_limit_time') * Minute : Day;
        Cache::put('userCheckIn_'.$user->id, '1', $ttl);

        return Response::json(['status' => 'success', 'message' => trans('user.home.attendance.success', ['data' => formatBytes($traffic)])]);
    }

    // 修改个人资料
    public function profile()
    {
        $user = auth()->user();
        $auth = $user->userAuths()->pluck('type')->toArray();

        return view('user.profile', compact('auth'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();
        // 修改密码
        if ($request->has(['password', 'new_password'])) {
            $data = $request->only(['password', 'new_password']);

            if (! Hash::check($data['password'], $user->password)) {
                return Redirect::back()->withErrors(trans('auth.password.reset.error.wrong'));
            }

            if (Hash::check($data['new_password'], $user->password)) {
                return Redirect::back()->withErrors(trans('auth.password.reset.error.same'));
            }

            // 演示环境禁止改管理员密码
            if ($user->id === 1 && config('app.env') === 'demo') {
                return Redirect::back()->withErrors(trans('auth.password.reset.error.demo'));
            }

            if (! $user->update(['password' => $data['new_password']])) {
                return Redirect::back()->withErrors(trans('common.failed_item', ['attribute' => trans('common.update')]));
            }

            return Redirect::back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.update')]));
            // 修改代理密码
        }

        if ($request->has('passwd')) {
            $passwd = $request->input('passwd');
            if (! $user->update(['passwd' => $passwd])) {
                return Redirect::back()->withErrors(trans('common.failed_item', ['attribute' => trans('common.update')]));
            }

            return Redirect::back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.update')]));
        }

        // 修改联系方式
        if ($request->has(['nickname', 'wechat', 'qq'])) {
            $data = $request->only(['nickname', 'wechat', 'qq']);
            if (empty($data['nickname'])) {
                return Redirect::back()->withErrors(trans('validation.required', ['attribute' => trans('model.user.nickname')]));
            }

            if (! $user->update($data)) {
                return Redirect::back()->withErrors(trans('common.failed_item', ['attribute' => trans('common.update')]));
            }
        }

        return Redirect::back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.update')]));
    }

    public function exchangeSubscribe(): ?JsonResponse
    { // 更换订阅地址
        try {
            DB::beginTransaction();
            $user = auth()->user();

            // 更换订阅码
            $user->subscribe->update(['code' => Helpers::makeSubscribeCode()]);

            // 更换连接信息
            $user->update(['passwd' => Str::random(), 'vmess_id' => Str::uuid()]);

            DB::commit();

            return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.change')])]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error(trans('user.subscribe.error').'：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.change')]).$e->getMessage()]);
        }
    }

    public function switchToAdmin(): JsonResponse
    { // 转换成管理员的身份
        if (! Session::has('admin')) {
            return Response::json(['status' => 'fail', 'message' => trans('http-statuses.401')]);
        }

        // 管理员信息重新写入user
        $user = auth()->loginUsingId(Session::pull('admin'));
        if ($user) {
            return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.toggle')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.toggle')])]);
    }

    public function switchCurrency(string $code): RedirectResponse
    { // 切换语言
        Session::put('currency', $code);

        return Redirect::back();
    }
}
