<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\User;
use App\Models\UserGroup;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Validator;

class UserGroupController extends Controller
{
    public function index(Request $request)
    {
        $view['list'] = UserGroup::paginate(15)->appends($request->except('page'));

        return view('admin.user.group.index', $view);
    }

    // 添加用户分组页面
    public function create()
    {
        $view['nodeList'] = Node::whereStatus(1)->get();

        return view('admin.user.group.info', $view);
    }

    // 添加用户分组
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), ['name' => 'required', 'nodes' => 'required']);

        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator->errors());
        }

        $userGroup = UserGroup::create(['name' => $request->input('name'), 'nodes' => $request->input('nodes')]);

        if ($userGroup) {
            return Redirect::route('admin.user.group.edit', $userGroup->id)->with('successMsg', '操作成功');
        }

        return Redirect::back()->withInput()->withErrors('操作失败');
    }

    // 编辑用户分组页面
    public function edit($id)
    {
        $view['userGroup'] = UserGroup::findOrFail($id);
        $view['nodeList'] = Node::whereStatus(1)->get();

        return view('admin.user.group.info', $view);
    }

    // 编辑用户分组
    public function update(Request $request, $id)
    {
        $userGroup = UserGroup::findOrFail($id);
        $userGroup->name = $request->input('name');
        $userGroup->nodes = $request->input('nodes');
        if ($userGroup->save()) {
            return Redirect::back()->with('successMsg', '操作成功');
        }

        return Redirect::back()->withInput()->withErrors('操作失败');
    }

    // 删除用户分组
    public function destroy($id): JsonResponse
    {
        // 校验该分组下是否存在关联账号
        if (User::whereGroupId($id)->count()) {
            return Response::json(['status' => 'fail', 'message' => '该分组下存在关联账号，请先取消关联！']);
        }

        try {
            UserGroup::whereId($id)->delete();
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
        }

        return Response::json(['status' => 'success', 'message' => '清理成功']);
    }
}
