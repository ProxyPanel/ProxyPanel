<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionRequest;
use Exception;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permission.index', ['permissions' => Permission::query()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.permission.info');
    }

    public function store(PermissionRequest $request)
    {
        if ($permission = Permission::create($request->validated())) {
            return redirect()->route('admin.permission.edit', $permission)->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permission.info', compact('permission'));
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        if ($permission->update($request->validated())) {
            return redirect()->back()->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    public function destroy(Permission $permission)
    {
        try {
            $permission->delete();
        } catch (Exception $e) {
            return response()->json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
        }

        return response()->json(['status' => 'success', 'message' => '清理成功']);
    }
}
