<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SsNode;
use App\Models\User;
use App\Models\UserGroup;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Validator;

class GroupController extends Controller {
	public function userGroupList(Request $request): \Illuminate\Http\Response {
		$view['list'] = UserGroup::query()->paginate(15)->appends($request->except('page'));
		return Response::view('admin.group.groupList', $view);
	}

	// 添加用户分组
	public function addUserGroup(Request $request) {
		if($request->isMethod('POST')){
			$validator = Validator::make($request->all(), [
				'name'  => 'required',
				'nodes' => 'required',
			]);

			if($validator->fails()){
				return Redirect::back()->withInput()->withErrors($validator->errors());
			}

			$ret = UserGroup::query()->insert([
				'name'  => $request->input('name'),
				'nodes' => implode(',', $request->input('nodes'))
			]);

			if($ret){
				return Redirect::back()->with('successMsg', '操作成功');
			}
			return Redirect::back()->withInput()->withErrors('操作失败');
		}
		$view['nodeList'] = SsNode::query()->whereStatus(1)->get();
		return Response::view('admin.group.groupInfo', $view);
	}

	// 编辑用户分组
	public function editUserGroup(Request $request) {
		$id = $request->input('id');
		if($request->isMethod('POST')){
			$validator = Validator::make($request->all(), [
				'id'   => 'required',
				'name' => 'required',
			]);

			if($validator->fails()){
				return Redirect::back()->withInput()->withErrors($validator->errors());
			}
			$name = $request->input('name');
			$nodes = $request->input('nodes');
			$userGroup = UserGroup::find($id);
			if(!$userGroup){
				return Redirect::back()->withInput()->withErrors('未找到需要编辑的用户分组！');
			}

			$data = [];
			if($userGroup->name != $name){
				$data['name'] = $name;
			}

			if($nodes){
				$nodeStr = implode(',', $nodes);
				if($userGroup->nodes != $nodeStr){
					$data['nodes'] = $nodeStr;
				}elseif($data == []){
					return Redirect::back()->with('successMsg', '检测为未修改，无变动！');
				}
			}elseif(isset($userGroup->nodes)){
				$data['nodes'] = $nodes;
			}
			$ret = UserGroup::query()->whereId($id)->update($data);
			if($ret){
				return Redirect::back()->with('successMsg', '操作成功');
			}
			return Redirect::back()->withInput()->withErrors('操作失败');
		}

		$userGroup = UserGroup::find($id);
		if(!$userGroup){
			return Redirect::back();
		}
		$view['nodeList'] = SsNode::query()->whereStatus(1)->get();

		return view('admin.group.groupInfo', $view)->with(compact('userGroup'));
	}

	// 删除用户分组
	public function delUserGroup(Request $request): JsonResponse {
		$id = $request->input('id');
		// 校验该分组下是否存在关联账号
		$userCount = User::query()->whereGroupId($id)->count();
		if($userCount){
			return Response::json(['status' => 'fail', 'message' => '该分组下存在关联账号，请先取消关联！']);
		}

		$userGroup = UserGroup::find($id);
		if(!$userGroup){
			return Response::json(['status' => 'fail', 'message' => '删除失败，未找到用户分组']);
		}

		try{
			UserGroup::query()->whereId($id)->delete();
		}catch(Exception $e){
			return Response::json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
		}

		return Response::json(['status' => 'success', 'message' => '清理成功']);
	}
}
