<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use ActionResponse;

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
        return $this->redirectAction('common.add', 'model.permission.attribute', function () use ($request) {
            $permission = Permission::create($request->validated());

            return redirect()->route('admin.permission.edit', $permission)->with('successMsg', trans('common.success_item', ['attribute' => trans('common.add')]));
        });
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
        return $this->redirectAction('common.update', 'model.permission.attribute', function () use ($request, $permission) {
            $permission->update($request->validated());

            return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.update')]));
        });
    }

    public function destroy(Permission $permission): JsonResponse
    {
        return $this->actionResponse('common.delete', 'model.permission.attribute', fn () => $permission->delete());
    }
}
