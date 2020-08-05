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

class UserGroupController extends Controller {
	public function index(): \Illuminate\Http\Response {
		$view['list'] = UserGroup::query()->paginate(15);
		return Response::view('admin.user.group.index', $view);
	}

	// 添加用户分组页面
	public function create(): \Illuminate\Http\Response {
		$view['nodeList'] = Node::query()->whereStatus(1)->get();
		return Response::view('admin.user.group.info', $view);
	}

	// 添加用户分组
	public function store(Request $request): RedirectResponse {
		$validator = Validator::make($request->all(), [
			'name'  => 'required',
			'nodes' => 'required',
		]);

		if($validator->fails()){
			return Redirect::back()->withInput()->withErrors($validator->errors());
		}

		$obj = new UserGroup();
		$obj->name = $request->input('name');
		$obj->nodes = $request->input('nodes');
		$obj->save();

		if($obj->id){
			return Redirect::back()->with('successMsg', '操作成功');
		}
		return Redirect::back()->withInput()->withErrors('操作失败');
	}

	// 编辑用户分组页面
	public function edit($id): \Illuminate\Http\Response {
		$view['userGroup'] = UserGroup::findOrFail($id);
		$view['nodeList'] = Node::query()->whereStatus(1)->get();

		return Response::view('admin.user.group.info', $view);
	}

	// 编辑用户分组
	public function update(Request $request, $id) {
		$userGroup = UserGroup::findOrFail($id);
		$userGroup->name = $request->input('name');
		$userGroup->nodes = $request->input('nodes');
		if($userGroup->save()){
			return Redirect::back()->with('successMsg', '操作成功');
		}

		return Redirect::back()->withInput()->withErrors('操作失败');
	}

	// 删除用户分组
	public function destroy($id): JsonResponse {
		// 校验该分组下是否存在关联账号
		if(User::query()->whereGroupId($id)->count()){
			return Response::json(['status' => 'fail', 'message' => '该分组下存在关联账号，请先取消关联！']);
		}

		try{
			UserGroup::query()->whereId($id)->delete();
		}catch(Exception $e){
			return Response::json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
		}

		return Response::json(['status' => 'success', 'message' => '清理成功']);
	}
}
