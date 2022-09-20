<?php

namespace App\Http\Controllers\Admin;

use App\Components\Helpers;
use App\Components\IP;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Jobs\VNet\getUser;
use App\Models\Level;
use App\Models\Node;
use App\Models\Order;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserHourlyDataFlow;
use App\Models\UserOauth;
use Arr;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Session;
use Spatie\Permission\Models\Role;
use Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('subscribe');

        foreach (['id', 'port', 'status', 'enable', 'user_group_id', 'level'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        foreach (['username', 'wechat', 'qq'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, 'like', "%{$value}%");
            });
        }

        // 流量超过100G的
        $request->whenFilled('largeTraffic', function () use ($query) {
            $query->whereIn('status', [0, 1])->whereRaw('(u + d)/transfer_enable >= 0.9');
        });

        // 临近过期提醒
        $request->whenFilled('expireWarning', function () use ($query) {
            $query->whereBetween('expired_at', [date('Y-m-d'), date('Y-m-d', strtotime(sysConfig('expire_days').' days'))]);
        });

        // 当前在线
        $request->whenFilled('online', function () use ($query) {
            $query->where('t', '>=', strtotime('-10 minutes'));
        });

        // 不活跃用户
        $request->whenFilled('unActive', function () use ($query) {
            $query->whereBetween('t', [1, strtotime('-'.sysConfig('expire_days').' days')])->whereEnable(1);
        });

        // 不活跃用户
        $request->whenFilled('paying', function () use ($query) {
            $payingUser = Order::whereStatus(2)->where('goods_id', '<>', null)->whereIsExpire(0)->where('amount', '>', 0)->pluck('user_id')->unique();
            $query->whereIn('id', $payingUser);
        });

        // 1小时内流量异常用户
        $request->whenFilled('flowAbnormal', function () use ($query) {
            $query->whereIn('id', (new UserHourlyDataFlow)->trafficAbnormal());
        });

        return view('admin.user.index', [
            'userList'   => $query->sortable(['id' => 'desc'])->paginate(15)->appends($request->except('page')),
            'userGroups' => UserGroup::all()->pluck('name', 'id')->toArray(),
            'levels'     => Level::all()->pluck('name', 'level')->toArray(),
        ]);
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        Arr::forget($data, 'roles');
        $data['password'] = $data['password'] ?? Str::random();
        $data['port'] = $data['port'] ?? Helpers::getPort();
        $data['passwd'] = $data['passwd'] ?? Str::random();
        $data['vmess_id'] = $data['uuid'] ?? Str::uuid();
        Arr::forget($data, 'uuid');
        $data['transfer_enable'] *= GB;
        $data['expired_at'] = $data['expired_at'] ?? date('Y-m-d', strtotime('365 days'));
        $data['remark'] = str_replace(['atob', 'eval'], '', $data['remark']);
        $data['reg_ip'] = IP::getClientIp();
        $data['reset_time'] = $data['reset_time'] > date('Y-m-d') ? $data['reset_time'] : null;
        $user = User::create($data);

        $roles = $request->input('roles');
        try {
            $adminUser = Auth::getUser();
            if ($roles && ($adminUser->can('give roles') || (in_array('Super Admin', $roles, true) && $adminUser->hasRole('Super Admin')))) {
                // 编辑用户权限
                // 只有超级管理员才有赋予超级管理的权限
                $user->assignRole($roles);
            }

            if ($user) {
                // 写入用户流量变动记录
                Helpers::addUserTrafficModifyLog($user->id, null, 0, $data['transfer_enable'], '后台手动添加用户');

                return Response::json(['status' => 'success', 'message' => '添加成功']);
            }
        } catch (Exception $e) {
            Log::error('添加用户错误：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '添加失败']);
    }

    public function create()
    {
        if (Auth::getUser()->hasRole('Super Admin')) { // 超级管理员直接获取全部角色
            $roles = Role::all()->pluck('description', 'name');
        } elseif (Auth::getUser()->can('give roles')) { // 有权者只能获得已有角色，防止权限泛滥
            $roles = Auth::getUser()->roles()->pluck('description', 'name');
        }

        return view('admin.user.info', [
            'levels'     => Level::orderBy('level')->get(),
            'userGroups' => UserGroup::orderBy('id')->get(),
            'roles'      => $roles ?? null,
        ]);
    }

    public function edit(User $user)
    {
        if (Auth::getUser()->hasRole('Super Admin')) { // 超级管理员直接获取全部角色
            $roles = Role::all()->pluck('description', 'name');
        } elseif (Auth::getUser()->can('give roles')) { // 有权者只能获得已有角色，防止权限泛滥
            $roles = Auth::getUser()->roles()->pluck('description', 'name');
        }

        return view('admin.user.info', [
            'user'       => $user->load('inviter:id,username'),
            'levels'     => Level::orderBy('level')->get(),
            'userGroups' => UserGroup::orderBy('id')->get(),
            'roles'      => $roles ?? null,
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->id === 1) {
            return Response::json(['status' => 'fail', 'message' => '系统管理员不可删除']);
        }

        try {
            if ($user->delete()) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('删除用户信息异常：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除失败'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }

    public function batchAddUsers()
    {
        try {
            for ($i = 0; $i < (int) request('amount', 1); $i++) {
                $user = Helpers::addUser(Str::random(8).'@auto.generate', Str::random(), 1024 * GB, 365);
                // 写入用户流量变动记录
                Helpers::addUserTrafficModifyLog($user->id, null, 0, 1024 * GB, '后台批量生成用户');
            }

            return Response::json(['status' => 'success', 'message' => '批量生成账号成功']);
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '批量生成账号失败：'.$e->getMessage()]);
        }
    }

    public function switchToUser(User $user): JsonResponse
    {
        // 存储当前管理员ID，并将当前登录信息改成要切换的用户的身份信息
        Session::put('admin', Auth::id());
        Session::put('user', $user->id);

        return Response::json(['status' => 'success', 'message' => '身份切换成功']);
    }

    public function resetTraffic(User $user): JsonResponse
    {
        try {
            $user->update(['u' => 0, 'd' => 0]);
        } catch (Exception $e) {
            Log::error('流量重置失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '流量重置失败']);
        }

        return Response::json(['status' => 'success', 'message' => '流量重置成功']);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $data = $request->validated();
        $data['passwd'] = $request->input('passwd') ?? Str::random();
        $data['vmess_id'] = $data['uuid'] ?? Str::uuid();
        Arr::forget($data, ['roles', 'uuid', 'password']);
        $data['transfer_enable'] *= GB;
        $data['enable'] = $data['status'] < 0 ? 0 : $data['enable'];
        $data['expired_at'] = $data['expired_at'] ?? date('Y-m-d', strtotime('365 days'));
        $data['remark'] = str_replace(['atob', 'eval'], '', $data['remark']);

        // 只有超级管理员才能赋予超级管理员
        $roles = $request->input('roles');
        try {
            if (isset($roles)) {
                $adminUser = Auth::getUser();
                if ($adminUser->can('give roles') || $adminUser->hasRole('Super Admin')
                    || (in_array('Super Admin', $roles, true) && Auth::getUser()->hasRole('Super Admin'))) {
                    $user->syncRoles($roles);
                }
            } else {
                $user->roles()->detach();
            }

            // Input checking for dummy
            if ($data['enable'] === '1') {
                if ($data['status'] === '-1' || $data['transfer_enable'] === 0 || $data['expired_at'] < date('Y-m-d')) {
                    $data['enable'] = 0;
                }
            }

            // 非演示环境才可以修改管理员密码
            $password = $request->input('password');
            if (! empty($password) && ! (config('app.demo') && $user->id === 1)) {
                $data['password'] = $password;
            }

            // 写入用户流量变动记录
            if ($user->transfer_enable !== $data['transfer_enable']) {
                Helpers::addUserTrafficModifyLog($user->id, null, $user->transfer_enable, $data['transfer_enable'], '后台手动编辑用户');
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

    public function handleUserCredit(Request $request, User $user): JsonResponse
    {
        $amount = $request->input('amount');

        if (empty($amount)) {
            return Response::json(['status' => 'fail', 'message' => '充值异常']);
        }

        // 加减余额
        if ($user->updateCredit($amount)) {
            Helpers::addUserCreditLog($user->id, null, $user->credit - $amount, $user->credit, $amount, $request->input('description') ?? '后台手动充值');  // 写入余额变动日志

            return Response::json(['status' => 'success', 'message' => '充值成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '充值失败']);
    }

    public function export(User $user)
    {
        return view('admin.user.export', [
            'user'     => $user,
            'nodeList' => Node::whereStatus(1)->orderByDesc('sort')->orderBy('id')->paginate(15)->appends(\request('page')),
        ]);
    }

    public function exportProxyConfig(Request $request, User $user): JsonResponse
    {
        $server = Node::findOrFail($request->input('id'))->getConfig($user); // 提取节点信息

        return Response::json(['status' => 'success', 'data' => $this->getUserNodeInfo($server, $request->input('type') !== 'text'), 'title' => $server['type']]);
    }

    public function oauth()
    {
        $list = UserOauth::with('user:id,username')->paginate(15)->appends(\request('page'));

        return view('admin.user.oauth', compact('list'));
    }

    public function VNetInfo(User $user)
    {
        $nodes = $user->nodes()->whereType(4)->get(['node.id', 'node.name']);
        $nodeList = (new getUser())->existsinVNet($user);

        foreach ($nodes as $node) {
            $node->avaliable = in_array($node->id, $nodeList, true) ? '✔️' : '❌';
        }

        return Response::json(['status' => 'success', 'data' => $nodes]);
    }
}
