<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return view('admin.role.index', ['roles' => Role::with('permissions')->paginate(15)]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        if ($role = Role::create($request->only(['name', 'description']))) {
            $role->givePermissionTo($request->input('permissions') ?? []);

            return redirect()->route('admin.role.edit', $role)->with('successMsg', trans('common.success_item', ['attribute' => trans('common.add')]));
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.add')]));
    }

    public function create()
    {
        return view('admin.role.info', ['permissions' => Permission::all()->pluck('description', 'name')]);
    }

    public function edit(Role $role)
    {
        return view('admin.role.info', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::all()->pluck('description', 'name'),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === 'Super Admin') {
            return redirect()->back()->withInput()->withErrors(trans('admin.role.modify_admin_error'));
        }

        if ($role->update($request->only(['name', 'description']))) {
            $role->syncPermissions($request->input('permissions') ?: []);

            return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.edit')]));
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
