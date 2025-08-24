<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionRequest;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request): View
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
        try {
            $permission = Permission::create($request->validated());

            return redirect()->route('admin.permission.edit', $permission)->with('successMsg', trans('common.success_item', ['attribute' => trans('common.add')]));
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('model.permission.attribute')]).': '.$e->getMessage());

            return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.add')]).', '.$e->getMessage());
        }
    }

    public function create(): View
    {
        return view('admin.permission.info');
    }

    public function edit(Permission $permission): View
    {
        return view('admin.permission.info', ['permission' => $permission->makeHidden(['created_at', 'updated_at', 'guard_name'])]);
    }

    public function update(PermissionRequest $request, Permission $permission): RedirectResponse
    {
        try {
            $permission->update($request->validated());

            return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.update')]));
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.update'), 'attribute' => trans('model.permission.attribute')]).': '.$e->getMessage());

            return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.update')]).', '.$e->getMessage());
        }
    }

    public function destroy(Permission $permission): JsonResponse
    {
        try {
            if ($permission->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.permission.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
