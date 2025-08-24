<?php

namespace App\Http\Controllers;

use App\Helpers\DataChart;
use App\Models\Article;
use App\Services\NodeService;
use App\Services\UserService;
use App\Utils\Helpers;
use DB;
use Exception;
use Hash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use Str;

class UserController extends Controller
{
    use DataChart;

    public function index(NodeService $nodeService, UserService $userService): View
    {
        if (session()->has('user')) { // 用户转换
            auth()->loginUsingId(session()->pull('user'));
        }
        $user = auth()->user();

        $user->load(['subscribe', 'latestLoginLog']);

        return view('user.index', [
            'remainDays' => $userService->getRemainingDays(),
            'resetDays' => $userService->getResetDays(),
            'unusedPercent' => $userService->getUnusedTrafficPercent(),
            'announcements' => cache()->remember('announcements_'.app()->getLocale(), 300, function () {
                return Article::type(2)->lang()->latest()->simplePaginate(1); // 公告缓存 5 分钟
            }), // 公告
            'isTrafficWarning' => $userService->isTrafficWarning(), // 流量异常判断
            'paying_user' => $userService->isActivePaying(), // 付费用户判断
            'user' => $user->only(['sub_url', 'unused_traffic', 'expiration_date', 'ban_time']),
            'userLoginLog' => $user->latestLoginLog,
            'subType' => $nodeService->getActiveNodeTypes($user->nodes()),
            'subscribe' => $user->subscribe->only(['status', 'ban_desc']),
            ...$this->dataFlowChart($user->id)]);
    }

    // 签到
    public function checkIn(): JsonResponse
    {
        $user = auth()->user();
        // 系统开启登录加积分功能才可以签到
        if (! sysConfig('checkin_interval')) {
            return response()->json(['status' => 'fail', 'title' => trans('common.failed'), 'message' => trans('user.home.attendance.disable')]);
        }

        // 已签到过，验证是否有效
        if (cache()->has('userCheckIn_'.$user->id)) {
            return response()->json(['status' => 'success', 'title' => trans('common.success'), 'message' => trans('user.home.attendance.done')]);
        }

        $traffic = random_int((int) sysConfig('checkin_reward'), (int) sysConfig('checkin_reward_max')) * MiB;

        if (! $user->incrementData($traffic)) {
            return response()->json(['status' => 'fail', 'title' => trans('common.failed'), 'message' => trans('user.home.attendance.failed')]);
        }

        Helpers::addUserTrafficModifyLog($user->id, $user->transfer_enable, $user->transfer_enable + $traffic, trans('user.home.attendance.attribute'));

        cache()->put('userCheckIn_'.$user->id, '1', sysConfig('checkin_interval') ? sysConfig('checkin_interval') * Minute : Day); // 多久后可以再签到

        return response()->json(['status' => 'success', 'message' => trans('user.home.attendance.success', ['data' => formatBytes($traffic)])]);
    }

    // 修改个人资料
    public function profile(): View
    {
        return view('user.profile', ['auth' => auth()->user()->userAuths()->pluck('type')->toArray()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $url = null;

        if ($request->has(['password', 'new_password'])) { // 修改密码
            $url = url()->previous().'#account';
            $data = $request->only(['password', 'new_password']);

            if (! Hash::check($data['password'], $user->password)) {
                return redirect($url)->withErrors(trans('auth.password.reset.error.wrong'));
            }

            if (Hash::check($data['new_password'], $user->password)) {
                return redirect($url)->withErrors(trans('auth.password.reset.error.same'));
            }

            // 演示环境禁止改管理员密码
            if ($user->id === 1 && config('app.env') === 'demo') {
                return redirect($url)->withErrors(trans('auth.password.reset.error.demo'));
            }

            if (! $user->update(['password' => $data['new_password']])) {
                return redirect($url)->withErrors(trans('common.failed_item', ['attribute' => trans('common.update')]));
            }
        }

        if ($request->has('passwd')) { // 修改代理密码
            $url = url()->previous().'#proxy';
            $passwd = $request->input('passwd');
            if (! $user->update(['passwd' => $passwd])) {
                return redirect($url)->withErrors(trans('common.failed_item', ['attribute' => trans('common.update')]));
            }
        }

        // 修改联系方式
        if ($request->has(['nickname', 'wechat', 'qq'])) {
            $url = url()->previous().'#contact';
            $data = $request->only(['nickname', 'wechat', 'qq']);
            if (empty($data['nickname'])) {
                return redirect($url)->withErrors(trans('validation.required', ['attribute' => trans('model.user.nickname')]));
            }

            if (! $user->update($data)) {
                return redirect($url)->withErrors(trans('common.failed_item', ['attribute' => trans('common.update')]));
            }
        }

        return redirect($url)->with('successMsg', trans('common.success_item', ['attribute' => trans('common.update')]));
    }

    public function exchangeSubscribe(): JsonResponse
    { // 更换订阅地址
        try {
            DB::beginTransaction();
            $user = auth()->user();

            // 更换订阅码
            $user->subscribe->update(['code' => Helpers::makeSubscribeCode()]);

            // 更换连接信息
            $user->update(['passwd' => Str::random(), 'vmess_id' => Str::uuid()]);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.change')])]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error(trans('user.subscribe.error').'：'.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.change')]).$e->getMessage()]);
        }
    }

    public function switchToAdmin(): JsonResponse
    { // 转换成管理员的身份
        if (! session()->has('admin')) {
            return response()->json(['status' => 'fail', 'message' => trans('http-statuses.401')]);
        }

        // 管理员信息重新写入user
        $user = auth()->loginUsingId(session()->pull('admin'));
        if ($user) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.toggle')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.toggle')])]);
    }

    public function switchCurrency(string $code): RedirectResponse
    { // 切换语言
        session()->put('currency', $code);

        return redirect()->back();
    }
}
