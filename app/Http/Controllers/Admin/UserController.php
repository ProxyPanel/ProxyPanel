<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ProxyConfig;
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
use App\Services\ProxyService;
use App\Utils\Helpers;
use App\Utils\IP;
use Arr;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Spatie\Permission\Models\Role;
use Str;

class UserController extends Controller
{
    use ProxyConfig;

    public function index(Request $request): View
    {
        $query = User::with(['subscribe:user_id,code']);

        foreach (['id', 'port', 'status', 'enable', 'user_group_id', 'level'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        foreach (['username', 'wechat', 'qq'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, 'like', "%$value%");
            });
        }

        // 流量使用超过90%的用户
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

        // 付费服务中的用户
        $request->whenFilled('paying', function () use ($query) {
            $payingUser = Order::whereStatus(2)->whereNotNull('goods_id')->whereIsExpire(0)->where('amount', '>', 0)->pluck('user_id')->unique();
            $query->whereIn('id', $payingUser);
        });

        // 1小时内流量异常用户
        $request->whenFilled('flowAbnormal', function () use ($query) {
            $query->whereIn('id', (new UserHourlyDataFlow)->trafficAbnormal());
        });

        return view('admin.user.index', [
            'userList' => $query->sortable(['id' => 'desc'])->paginate(15)->appends($request->except('page')),
            'userGroups' => UserGroup::pluck('name', 'id'),
            'levels' => Level::orderBy('level')->pluck('name', 'level'),
        ]);
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        Arr::forget($data, 'roles');
        $data['password'] = $data['password'] ?? Str::random();
        $data['port'] = $data['port'] ?? Helpers::getPort();
        $data['passwd'] = $data['passwd'] ?? Str::random();
        $data['vmess_id'] = $data['vmess_id'] ?: Str::uuid();
        $data['transfer_enable'] *= GiB;
        $data['expired_at'] = $data['expired_at'] ?? date('Y-m-d', strtotime('next year'));
        $data['remark'] = str_replace(['atob', 'eval'], '', $data['remark'] ?? '');
        $data['reg_ip'] = IP::getClientIp();
        $data['reset_time'] = $data['reset_time'] > date('Y-m-d') ? $data['reset_time'] : null;
        $user = User::create($data);

        $roles = $request->input('roles');
        try {
            $editor = auth()->user();
            if ($roles && ($editor->can('give roles') || (in_array('Super Admin', $roles, true) && $editor->hasRole('Super Admin')))) {
                // 编辑用户权限, 只有超级管理员才有赋予超级管理的权限
                $user->assignRole($roles);
            }

            if ($user) {
                Helpers::addUserTrafficModifyLog($user->id, 0, $data['transfer_enable'], trans('Manually add in dashboard.'));

                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('model.user.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')])]);
    }

    public function create(): View
    {
        return view('admin.user.info', $this->getUserViewData());
    }

    public function edit(User $user): View
    {
        return view('admin.user.info', [...$this->getUserViewData(), 'user' => $user->load('inviter:id,username')]);
    }

    /**
     * 获取用户创建/编辑页面的共享数据.
     */
    private function getUserViewData(): array
    {
        $editor = auth()->user();
        $roles = null;
        if ($editor->hasRole('Super Admin')) { // 超级管理员直接获取全部角色
            $roles = Role::pluck('description', 'name');
        }

        if ($editor->can('give roles')) { // 有权者只能获得已有角色，防止权限泛滥
            $roles = $editor->roles()->pluck('description', 'name');
        }

        return [
            'levels' => Level::orderBy('level')->pluck('name', 'level'),
            'userGroups' => UserGroup::orderBy('id')->pluck('name', 'id'),
            'roles' => $roles,
            ...$this->proxyConfigOptions(),
        ];
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === 1) {
            return response()->json(['status' => 'fail', 'message' => trans('admin.user.admin_deletion')]);
        }

        try {
            if ($user->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.user.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }

    public function batchAddUsers(): JsonResponse
    {
        try {
            for ($i = 0; $i < (int) request('amount', 1); $i++) {
                $user = Helpers::addUser(Str::random(8).'@auto.generate', Str::random(), MiB * sysConfig('default_traffic'), (int) sysConfig('default_days'));
                Helpers::addUserTrafficModifyLog($user->id, 0, $user->transfer_enable, trans('Batch generate user accounts in dashboard.'));
            }

            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.generate')])]);
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.generate'), 'attribute' => trans('model.user.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.generate')]).', '.$e->getMessage()]);
        }
    }

    public function switchToUser(User $user): JsonResponse
    {
        // 存储当前管理员ID，并将当前登录信息改成要切换的用户的身份信息
        session()->put('admin', auth()->id());
        session()->put('user', $user->id);

        return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('admin.user.info.switch')])]);
    }

    public function resetTraffic(User $user): JsonResponse
    {
        try {
            if ($user->update(['u' => 0, 'd' => 0])) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.reset')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.reset'), 'attribute' => trans('model.user.usable_traffic')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.reset').', '.$e->getMessage()])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.reset')])]);
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        Arr::forget($data, ['roles', 'password']);
        $data['passwd'] = $request->input('passwd') ?? Str::random();
        $data['vmess_id'] = $data['vmess_id'] ?: Str::uuid();
        $data['transfer_enable'] *= GiB;
        $data['enable'] = $data['status'] < 0 ? 0 : $data['enable'];
        $data['expired_at'] = $data['expired_at'] ?? date('Y-m-d', strtotime('next year'));
        if ($data['remark']) {
            $data['remark'] = str_replace(['atob', 'eval'], '', $data['remark']);
        }

        // 只有超级管理员才能赋予超级管理员
        $roles = $request->input('roles');
        try {
            if (isset($roles)) {
                $editor = auth()->user();
                if ($editor->can('give roles') || $editor->hasRole('Super Admin')
                    || (in_array('Super Admin', $roles, true) && auth()->user()->hasRole('Super Admin'))) {
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
            if (! empty($password) && (config('app.env') !== 'demo' || $user->id !== 1)) {
                $data['password'] = $password;
            }

            if ($user->transfer_enable !== $data['transfer_enable']) {
                Helpers::addUserTrafficModifyLog($user->id, $user->transfer_enable, $data['transfer_enable'], trans('Manually edit in dashboard.'));
            }

            if ($user->update($data)) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.edit')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.edit'), 'attribute' => trans('model.user.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit').', '.$e->getMessage()])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')])]);
    }

    public function handleUserCredit(Request $request, User $user): JsonResponse
    {
        $amount = $request->input('amount');

        if (empty($amount)) {
            return response()->json(['status' => 'fail', 'message' => trans('common.error_item', ['attribute' => trans('user.recharge')])]);
        }

        // 加减余额
        if ($user->updateCredit($amount)) {
            Helpers::addUserCreditLog($user->id, null, $user->credit - $amount, $user->credit, $amount, $request->input('description') ?? 'Manually edit in dashboard.');  // 写入余额变动日志

            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('user.recharge')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('user.recharge')])]);
    }

    public function export(User $user): View
    {
        return view('admin.user.export', [
            'user' => $user,
            'nodeList' => Node::whereStatus(1)->orderByDesc('sort')->orderBy('id')->paginate(15)->appends(\request('page')),
        ]);
    }

    public function exportProxyConfig(Request $request, User $user, ProxyService $proxyService): JsonResponse
    {
        $proxyService->setUser($user);
        $server = $proxyService->getProxyConfig(Node::findOrFail($request->input('id')));

        return response()->json(['status' => 'success', 'data' => $proxyService->getUserProxyConfig($server, $request->input('type') !== 'text'), 'title' => $server['type']]);
    }

    public function oauth(Request $request): View
    {
        $query = UserOauth::with('user:id,username');

        // 用户名过滤
        $request->whenFilled('username', function ($value) use ($query) {
            $query->whereHas('user', function ($userQuery) use ($value) {
                $userQuery->where('username', 'like', "%$value%");
            });
        });

        // 类型过滤
        $request->whenFilled('type', function ($value) use ($query) {
            $query->where('type', $value);
        });

        return view('admin.user.oauth', [
            'list' => $query->paginate(15)->appends(\request('page')),
        ]);
    }

    public function VNetInfo(User $user): JsonResponse
    {
        $nodes = $user->nodes()->whereType(4)->get(['node.id', 'node.name']);
        $nodeList = (new getUser)->existsinVNet($user);

        foreach ($nodes as $node) {
            $node->avaliable = in_array($node->id, $nodeList, true) ? '✔️' : '❌';
        }

        return response()->json(['status' => 'success', 'data' => $nodes]);
    }
}
