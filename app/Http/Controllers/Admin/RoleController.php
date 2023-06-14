<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

            return redirect()->route('admin.role.edit', $role)->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
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
            return redirect()->back()->withInput()->withErrors('请勿修改超级管理员');
        }

        if ($role->update($request->only(['name', 'description']))) {
            $role->syncPermissions($request->input('permissions') ?: []);

            return redirect()->back()->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    public function destroy(Role $role): JsonResponse
    {
        try {
            if ($role->name === 'Super Admin') {
                return response()->json(['status' => 'fail', 'message' => '请勿删除超级管理员']);
            }
            $role->delete();
        } catch (Exception $e) {
            return response()->json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
        }

        return response()->json(['status' => 'success', 'message' => '清理成功']);
    }
}
