<?php

namespace App\Http\Controllers\Admin;

use App\Components\Helpers;
use App\Components\IP;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\Level;
use App\Models\Node;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserHourlyDataFlow;
use App\Services\UserService;
use Auth;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Redirect;
use Response;
use Session;
use Str;

class UserController extends Controller
{
    // 用户列表
    public function index(Request $request)
    {
        $id = $request->input('id');
        $email = $request->input('email');
        $wechat = $request->input('wechat');
        $qq = $request->input('qq');
        $port = $request->input('port');
        $group = $request->input('group');
        $level = $request->input('level');
        $status = $request->input('status');
        $enable = $request->input('enable');
        $online = $request->input('online');
        $flowAbnormal = $request->input('flowAbnormal');
        $expireWarning = $request->input('expireWarning');
        $largeTraffic = $request->input('largeTraffic');

        $query = User::with('subscribe');
        if (isset($id)) {
            $query->whereId($id);
        }

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

        if (isset($status)) {
            $query->whereStatus($status);
        }

        if (isset($enable)) {
            $query->whereEnable($enable);
        }

        if (isset($group)) {
            $query->whereGroupId($group);
        }

        if (isset($level)) {
            $query->whereLevel($level);
        }

        // 流量超过100G的
        if ($largeTraffic) {
            $query->whereIn('status', [0, 1])->whereRaw('(u + d) >= 107374182400');
        }

        // 临近过期提醒
        if ($expireWarning) {
            $query->whereBetween('expired_at', [date('Y-m-d'), date('Y-m-d', strtotime("+".sysConfig('expire_days')." days"))]);
        }

        // 当前在线
        if ($online) {
            $query->where('t', '>=', strtotime("-10 minutes"));
        }

        // 不活跃用户
        if ($request->input('unActive')) {
            $query->whereBetween('t', [1, strtotime("-".sysConfig('expire_days')." days"),])->whereEnable(1);
        }

        // 1小时内流量异常用户
        if ($flowAbnormal) {
            $query->whereIn('id', $this->trafficAbnormal());
        }

        $userList = $query->orderByDesc('id')->paginate(15)->appends($request->except('page'));
        foreach ($userList as $user) {
            $user->used_flow = flowAutoShow($user->u + $user->d);
            if ($user->expired_at < date('Y-m-d')) {
                $user->expireWarning = -1; // 已过期
            } elseif ($user->expired_at == date('Y-m-d')) {
                $user->expireWarning = 0; // 今天过期
            } elseif ($user->expired_at > date('Y-m-d') && $user->expired_at <= date('Y-m-d', strtotime("+30 days"))) {
                $user->expireWarning = 1; // 最近一个月过期
            } else {
                $user->expireWarning = 2; // 大于一个月过期
            }

            // 流量异常警告
            $totalTraffic = UserHourlyDataFlow::userRecentUsed($user->id)->sum('total');
            $user->trafficWarning = $totalTraffic > (sysConfig('traffic_ban_value') * GB) ? 1 : 0;

            // 订阅地址
            $user->link = (sysConfig('subscribe_domain') ?: sysConfig('website_url')).'/s/'.$user->subscribe->code;
        }

        return view('admin.user.index', [
            'userList'   => $userList,
            'userGroups' => UserGroup::all()->pluck('name', 'id')->toArray(),
            'levels'     => Level::all()->pluck('name', 'level')->toArray(),
        ]);
    }

    // 1小时内流量异常用户
    private function trafficAbnormal(): array
    {
        $userTotalTrafficList = UserHourlyDataFlow::whereNodeId(0)
            ->where('total', '>', MB * 50)
            ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))
            ->groupBy('user_id')
            ->selectRaw("user_id, sum(total) as totalTraffic")->pluck('totalTraffic', 'user_id')
            ->toArray(); // 只统计50M以上的记录，加快速度
        foreach ($userTotalTrafficList as $user) {
            if ($user->totalTraffic > sysConfig('traffic_ban_value') * GB) {
                $result[] = $user->user_id;
            }
        }

        return $result ?? [];
    }

    // 添加账号页面
    public function create()
    {
        return view('admin.user.info', [
            'levelList' => Level::orderBy('level')->get(),
            'groupList' => UserGroup::orderBy('id')->get(),
        ]);
    }

    // 添加账号
    public function store(UserStoreRequest $request): JsonResponse
    {
        try {
            $data = $request->except('_token', 'uuid');
            $data['password'] = $data['password'] ?? Str::random();
            $data['port'] = $data['port'] ?? Helpers::getPort();
            $data['passwd'] = $data['passwd'] ?? Str::random();
            $data['vmess_id'] = $request->input('uuid') ?? Str::uuid();
            $data['expired_at'] = $data['expired_at'] ?? date('Y-m-d', strtotime("+365 days"));
            $data['remark'] = str_replace(["atob", "eval"], "", $data['remark']);
            $data['reg_ip'] = IP::getClientIp();
            $data['reset_time'] = $data['reset_time'] > date('Y-m-d') ? $data['reset_time'] : null;
            $user = User::create($data);

            if ($user) {
                // 写入用户流量变动记录
                Helpers::addUserTrafficModifyLog($user->id, 0, 0, $data['transfer_enable'], '后台手动添加用户');

                return Response::json(['status' => 'success', 'message' => '添加成功']);
            }
        } catch (Exception $e) {
            Log::error('添加用户错误：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => $e->getMessage()]);
        }


        return Response::json(['status' => 'fail', 'message' => '添加失败']);
    }

    // 编辑账号页面
    public function edit($id)
    {
        $user = User::find($id);

        return view('admin.user.info', [
            'user'      => $user->load('inviter:id,email'),
            'levelList' => Level::orderBy('level')->get(),
            'groupList' => UserGroup::orderBy('id')->get(),
        ]);
    }

    // 编辑账号
    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::find($id);

        try {
            $data = $request->except('_token', 'password', 'uuid', 'password', 'is_admin');
            $data['passwd'] = $request->input('passwd') ?? Str::random();
            $data['vmess_id'] = $request->input('uuid') ?? Str::uuid();
            $data['transfer_enable'] *= GB;
            $data['enable'] = $data['status'] < 0 ? 0 : $data['enable'];
            $data['expired_at'] = $data['expired_at'] ?? date('Y-m-d', strtotime("+365 days"));
            $data['remark'] = str_replace(["atob", "eval"], "", $data['remark']);

            // 只有admin才有权限操作管理员属性
            if (Auth::getUser()->is_admin === 1) {
                $data['is_admin'] = (int) $request->input('is_admin');
            }

            // 非演示环境才可以修改管理员密码
            $password = $request->input('password');
            if (!empty($password) && !(env('APP_DEMO') && $id === 1)) {
                $data['password'] = $password;
            }

            // 写入用户流量变动记录
            if ($user->transfer_enable !== $data['transfer_enable']) {
                Helpers::addUserTrafficModifyLog($id, 0, $user->transfer_enable, $data['transfer_enable'], '后台手动编辑用户');
            }

            if ($user->update($data)) {
                return Response::json(['status' => 'success', 'message' => '编辑成功']);
            }
        } catch (Exception $e) {
            Log::error('编辑用户信息异常：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '编辑用户信息错误：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '编辑失败']);
    }

    // 删除用户
    public function destroy($id)
    {
        if ($id <= 1) {
            return Response::json(['status' => 'fail', 'message' => '系统管理员不可删除']);
        }

        try {
            DB::beginTransaction();

            User::find($id)->delete();

            DB::commit();

            return Response::json(['status' => 'success', 'message' => '删除成功']);
        } catch (Exception $e) {
            Log::error('删除用户信息异常：'.$e->getMessage());
            DB::rollBack();

            return Response::json(['status' => 'fail', 'message' => '删除失败']);
        }
    }

    // 批量生成账号
    public function batchAddUsers(Request $request)
    {
        try {
            DB::beginTransaction();

            for ($i = 0; $i < $request->input('amount', 1); $i++) {
                $uid = Helpers::addUser(Str::random(8).'@auto.generate', Str::random(), 1024 * GB, 365);

                if ($uid) {
                    // 写入用户流量变动记录
                    Helpers::addUserTrafficModifyLog($uid, 0, 0, 1024 * GB, '后台批量生成用户');
                }
            }

            DB::commit();

            return Response::json(['status' => 'success', 'message' => '批量生成账号成功']);
        } catch (Exception $e) {
            DB::rollBack();

            return Response::json(['status' => 'fail', 'message' => '批量生成账号失败：'.$e->getMessage()]);
        }
    }

    // 转换成某个用户的身份
    public function switchToUser(Request $request): JsonResponse
    {
        $id = $request->input('user_id');

        $user = User::find($id);
        if (!$user) {
            return Response::json(['status' => 'fail', 'message' => "用户不存在"]);
        }

        // 存储当前管理员ID，并将当前登录信息改成要切换的用户的身份信息
        Session::put('admin', Auth::id());
        Auth::login($user);

        return Response::json(['status' => 'success', 'message' => "身份切换成功"]);
    }

    // 重置用户流量
    public function resetTraffic(Request $request): JsonResponse
    {
        try {
            User::find($request->input('id'))->update(['u' => 0, 'd' => 0]);
        } catch (Exception $e) {
            Log::error('流量重置失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '流量重置失败']);
        }

        return Response::json(['status' => 'success', 'message' => '流量重置成功']);
    }

    // 操作用户余额
    public function handleUserCredit(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $amount = $request->input('amount');

        if (empty($userId) || empty($amount)) {
            return Response::json(['status' => 'fail', 'message' => '充值异常']);
        }
        $user = User::find($userId);

        // 加减余额
        if ((new UserService($user))->updateCredit($amount)) {
            Helpers::addUserCreditLog($userId, 0, $user->credit, $user->credit + $amount, $amount, '后台手动充值');  // 写入余额变动日志

            return Response::json(['status' => 'success', 'message' => '充值成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '充值失败']);
    }

    // 导出配置信息
    public function export(Request $request, $id)
    {
        if (empty($id)) {
            return Redirect::back();
        }

        $user = User::find($id);
        if (empty($user)) {
            return Redirect::back();
        }

        $view['nodeList'] = Node::whereStatus(1)->orderByDesc('sort')->orderBy('id')->paginate(15)->appends($request->except('page'));
        $view['user'] = $user;

        return view('admin.user.export', $view);
    }

    public function exportProxyConfig(Request $request, $uid): JsonResponse
    {
        $node = Node::find($request->input('id'));
        if ($node->type === 1) {
            if ($node->compatible) {
                $proxyType = 'SS';
            } else {
                $proxyType = 'SSR';
            }
        } else {
            $proxyType = 'V2Ray';
        }

        $data = $this->getUserNodeInfo($uid, $node->id, $request->input('type') !== 'text' ? 0 : 1);

        return Response::json(['status' => 'success', 'data' => $data, 'title' => $proxyType]);
    }
}
