<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserGroupRequest;
use App\Models\Node;
use App\Models\UserGroup;
use Exception;

class UserGroupController extends Controller
{
    public function index()
    {
        return view('admin.user.group.index', ['groups' => UserGroup::paginate(15)->appends(request('page'))]);
    }

    // 添加用户分组页面
    public function create()
    {
        return view('admin.user.group.info', ['nodes' => Node::whereStatus(1)->pluck('name', 'id')]);
    }

    // 添加用户分组
    public function store(UserGroupRequest $request)
    {
        if ($userGroup = UserGroup::create($request->only(['name']))) {
            $userGroup->nodes()->attach($request->input('nodes'));

            return redirect(route('admin.user.group.edit', $userGroup))->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    // 编辑用户分组页面
    public function edit(UserGroup $group)
    {
        return view('admin.user.group.info', [
            'group' => $group,
            'nodes' => Node::whereStatus(1)->pluck('name', 'id'),
        ]);
    }

    // 编辑用户分组
    public function update(UserGroupRequest $request, UserGroup $group)
    {
        if ($group->update($request->only(['name']))) {
            $group->nodes()->sync($request->input('nodes'));

            return redirect()->back()->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    // 删除用户分组
    public function destroy(UserGroup $group)
    {
        // 校验该分组下是否存在关联账号
        if ($group->users->isNotEmpty()) {
            return response()->json(['status' => 'fail', 'message' => '该分组下存在关联账号，请先取消关联！']);
        }

        try {
            $group->delete();
        } catch (Exception $e) {
            return response()->json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
        }

        return response()->json(['status' => 'success', 'message' => '清理成功']);
    }
}
