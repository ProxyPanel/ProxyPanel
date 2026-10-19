<?php

namespace App\Http\Controllers;

use App\Helpers\ActionResponse;
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
    use ActionResponse;
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
            'announcements' => Article::type(2)->lang()->latest()->simplePaginate(1), // 公告
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
        $result = (new UserService(auth()->user()))->checkIn();

        return match ($result['state']) {
            'disabled' => response()->json(['status' => 'fail', 'title' => trans('common.failed'), 'message' => $result['message']]),
            'repeated' => response()->json(['status' => 'success', 'title' => trans('common.success'), 'message' => $result['message']]),
            'failed' => response()->json(['status' => 'fail', 'title' => trans('common.failed'), 'message' => $result['message']]),
            default => response()->json(['status' => 'success', 'message' => $result['message']]),
        };
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

            return $this->successItem('common.change');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error(trans('user.subscribe.error').'：'.$e->getMessage());

            // 原文只进日志：这条 message 前端按 HTML 插入
            return $this->failItem('common.change');
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
            return $this->successItem('common.toggle');
        }

        return $this->failItem('common.toggle');
    }

    public function switchCurrency(string $code): RedirectResponse
    { // 切换语言
        session()->put('currency', $code);

        return redirect()->back();
    }
}
