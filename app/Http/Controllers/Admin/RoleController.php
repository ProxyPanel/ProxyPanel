<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->paginate(15);

        return view('admin.role.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all()->pluck('description', 'name');

        return view('admin.role.info', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), ['name' => 'required', 'description' => 'required']);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        $role = Role::create($request->except('permissions'));
        $permissions = $request->input('permissions') ?: [];
        if ($role->givePermissionTo($permissions)) {
            return redirect()->route('admin.role.edit', $role)->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all()->pluck('description', 'name');

        return view('admin.role.info', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validator = validator()->make($request->all(), ['name' => 'required', 'description' => 'required']);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        if ($role->name === 'Super Admin') {
            return redirect()->back()->withInput()->withErrors('请勿修改超级管理员');
        }

        $role->update($request->except('permissions'));
        $permissions = $request->input('permissions') ?: [];
        if ($role->syncPermissions($permissions)) {
            return redirect()->back()->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    public function destroy(Role $role)
    {
        try {
            if ($role->name === 'Super Admin') {
                return Response::json(['status' => 'fail', 'message' => '请勿删除超级管理员']);
            }
            $role->delete();
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
        }

        return Response::json(['status' => 'success', 'message' => '清理成功']);
    }
}
