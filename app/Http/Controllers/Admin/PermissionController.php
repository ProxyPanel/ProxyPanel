<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::query()->paginate(15);

        return view('admin.permission.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permission.info');
    }

    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), ['name' => 'required', 'description' => 'required']);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        $permission = Permission::create($request->all());

        if ($permission) {
            return redirect()->route('admin.permission.edit', $permission)->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permission.info', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validator = validator()->make($request->all(), ['name' => 'required', 'description' => 'required']);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        if ($permission->update($request->all())) {
            return redirect()->back()->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    public function destroy(Permission $permission)
    {
        try {
            $permission->delete();
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
        }

        return Response::json(['status' => 'success', 'message' => '清理成功']);
    }
}
