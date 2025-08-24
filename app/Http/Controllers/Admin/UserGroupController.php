<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserGroupRequest;
use App\Models\Node;
use App\Models\UserGroup;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Log;

class UserGroupController extends Controller
{
    public function index(): View
    {
        return view('admin.user.group.index', ['groups' => UserGroup::paginate(15)->appends(request('page'))]);
    }

    public function store(UserGroupRequest $request): RedirectResponse
    {
        if ($userGroup = UserGroup::create($request->only(['name']))) {
            $userGroup->nodes()->attach($request->input('nodes'));

            return redirect(route('admin.user.group.edit', $userGroup))->with('successMsg', trans('common.success_item', ['attribute' => trans('common.add')]));
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.add')]));
    }

    public function create(): View
    {
        return view('admin.user.group.info', ['nodes' => Node::whereStatus(1)->pluck('name', 'id')]);
    }

    public function edit(UserGroup $group): View
    {
        $group->load('nodes:id');

        return view('admin.user.group.info', [
            'group' => array_merge(
                $group->toArray(),
                ['nodes' => $group->nodes->pluck('id')->map('strval')->toArray()]
            ),
            'nodes' => Node::whereStatus(1)->pluck('name', 'id'),
        ]);
    }

    public function update(UserGroupRequest $request, UserGroup $group): RedirectResponse
    {
        if ($group->update($request->only(['name']))) {
            $group->nodes()->sync($request->input('nodes'));

            return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.edit')]));
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.edit')]));
    }

    public function destroy(UserGroup $group): JsonResponse
    {
        if ($group->users->isNotEmpty()) { // 校验该分组下是否存在关联账号
            return response()->json(['status' => 'fail', 'message' => trans('common.exists_error', ['attribute' => trans('model.user_group.attribute')])]);
        }

        try {
            if ($group->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.user_group.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
