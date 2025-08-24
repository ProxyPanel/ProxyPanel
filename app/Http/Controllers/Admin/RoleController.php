<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        // 预加载角色权限，但只选择需要的字段
        $roles = Role::with('permissions:description,name')->paginate(15);

        // 预先处理权限描述，避免在 Blade 模板中重复处理
        $processedRoles = $roles->through(function ($role) {
            if ($role->name !== 'Super Admin') {
                // 提前获取权限描述集合，避免在模板中重复调用
                $role->permission_descriptions = $role->permissions->pluck('description');
            }

            return $role;
        });

        return view('admin.role.index', ['roles' => $processedRoles]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        try {
            $role = Role::create($request->only(['name', 'description']));

            if ($role) {
                $permissions = $request->input('permissions') ?? [];
                if (! empty($permissions)) {
                    $role->givePermissionTo($permissions);
                }

                return redirect()->route('admin.role.edit', $role)->with('successMsg', trans('common.success_item', ['attribute' => trans('common.add')]));
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('model.role.attribute')]).': '.$e->getMessage());

            return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.add')]).', '.$e->getMessage());
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.add')]));
    }

    public function create(): View
    {
        return view('admin.role.info', ['permissions' => Permission::orderBy('name')->pluck('description', 'name')]);
    }

    public function edit(Role $role): View
    {
        $role->load('permissions:name');

        return view('admin.role.info', [
            'role' => array_merge(
                $role->toArray(),
                ['permissions' => $role->permissions->pluck('name')->toArray()]
            ),
            'permissions' => Permission::orderBy('name')->pluck('description', 'name'),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === 'Super Admin') {
            return redirect()->back()->withInput()->withErrors(trans('admin.role.modify_admin_error'));
        }

        try {
            if ($role->update($request->only(['name', 'description']))) {
                $role->syncPermissions($request->input('permissions', []));

                return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.edit')]));
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.edit'), 'attribute' => trans('model.role.attribute')]).': '.$e->getMessage());

            return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.edit')]).', '.$e->getMessage());
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.edit')]));
    }

    public function destroy(Role $role): JsonResponse
    {
        try {
            if ($role->name === 'Super Admin') {
                return response()->json(['status' => 'fail', 'message' => trans('admin.role.modify_admin_error')]);
            }

            $role->delete();
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.role.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
    }
}
