<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query();

        foreach (['name', 'description'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, 'like', "%$value%");
            });
        }

        return view('admin.permission.index', ['permissions' => $query->paginate(20)->appends($request->except('page'))]);
    }

    public function store(PermissionRequest $request): RedirectResponse
    {
        if ($permission = Permission::create($request->validated())) {
            return redirect()->route('admin.permission.edit', $permission)->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    public function create()
    {
        return view('admin.permission.info');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permission.info', compact('permission'));
    }

    public function update(PermissionRequest $request, Permission $permission): RedirectResponse
    {
        if ($permission->update($request->validated())) {
            return redirect()->back()->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    public function destroy(Permission $permission): JsonResponse
    {
        try {
            $permission->delete();
        } catch (Exception $e) {
            return response()->json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
        }

        return response()->json(['status' => 'success', 'message' => '清理成功']);
    }
}
