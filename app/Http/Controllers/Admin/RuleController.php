<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rule;
use App\Models\RuleGroup;
use App\Models\RuleGroupNode;
use App\Models\RuleLog;
use App\Models\SsNode;
use Exception;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Validator;

class RuleController extends Controller {
	// 审计规则列表
	public function ruleList(Request $request) {
		$type = $request->input('type');
		$query = Rule::query();

		if($type){
			$query->whereType($type);
		}

		$view['rules'] = $query->paginate(15)->appends($request->except('page'));
		return Response::view('admin.rule.ruleList', $view);
	}

	// 添加审计规则
	public function addRule(Request $request) {
		$validator = Validator::make($request->all(), [
			'type'    => 'required|between:1,4',
			'name'    => 'required',
			'pattern' => 'required',
		]);

		if($validator->fails()){
			return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
		}

		$obj = new Rule();
		$obj->type = $request->input('type');
		$obj->name = $request->input('name');
		$obj->pattern = $request->input('pattern');
		$obj->save();

		if($obj->id){
			return Response::json(['status' => 'success', 'data' => '', 'message' => '提交成功']);
		}else{
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
		}
	}

	// 编辑审计规则
	public function editRule(Request $request) {
		$validator = Validator::make($request->all(), [
			'id'           => 'required|exists:rule,id',
			'rule_name'    => 'required',
			'rule_pattern' => 'required',
		]);

		if($validator->fails()){
			return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
		}

		$ret = Rule::query()->whereId($request->input('id'))->update([
			'name'    => $request->input('rule_name'),
			'pattern' => $request->input('rule_pattern')
		]);
		if($ret){
			return Response::json(['status' => 'success', 'message' => '操作成功']);
		}else{
			return Response::json(['status' => 'fail', 'message' => '操作失败']);
		}

	}

	// 删除审计规则
	public function delRule(Request $request) {
		$id = $request->input('id');
		try{
			Rule::query()->whereId($id)->delete();

			$RuleGroupList = RuleGroup::query()->get();
			foreach($RuleGroupList as $RuleGroup){
				$rules = explode(',', $RuleGroup->rules);
				if(in_array($id, $rules)){
					$rules = implode(',', array_diff($rules, (array) $id));
					RuleGroup::query()->whereId($RuleGroup->id)->update(['rules' => $rules]);
				}
			}
		}catch(Exception $e){
			return Response::json(['status' => 'fail', 'message' => '操作失败, '.$e->getMessage()]);
		}
		return Response::json(['status' => 'success', 'message' => '操作成功']);
	}

	// 审计规则分组列表
	public function ruleGroupList(Request $request) {
		$view['ruleGroupList'] = RuleGroup::query()->paginate(15)->appends($request->except('page'));
		return Response::view('admin.rule.ruleGroupList', $view);
	}

	// 添加审计规则分组
	public function addRuleGroup(Request $request) {
		if($request->isMethod('POST')){
			$validator = Validator::make($request->all(), [
				'name'  => 'required',
				'type'  => 'required|boolean',
				'rules' => 'required',
			]);

			if($validator->fails()){
				return Redirect::back()->withInput()->withErrors($validator->errors());
			}

			$obj = new RuleGroup();
			$obj->name = $request->input('name');
			$obj->type = intval($request->input('type'));
			$obj->rules = implode(',', $request->input('rules'));
			$obj->save();

			if($obj->id){
				return Redirect::back()->with('successMsg', '操作成功');
			}else{
				return Redirect::back()->withInput()->withErrors('操作失败');
			}

		}else{
			$view['ruleList'] = Rule::query()->get();
			return Response::view('admin.rule.ruleGroupInfo', $view);
		}
	}

	// 编辑审计规则分组
	public function editRuleGroup(Request $request) {
		$id = $request->input('id');
		if($request->isMethod('POST')){
			$validator = Validator::make($request->all(), [
				'id'   => 'required',
				'name' => 'required',
				'type' => 'required|boolean'
			]);

			if($validator->fails()){
				return Redirect::back()->withInput()->withErrors($validator->errors());
			}
			$name = $request->input('name');
			$type = intval($request->input('type'));
			$rules = $request->input('rules');
			$ruleGroup = RuleGroup::query()->find($id);
			if(!$ruleGroup){
				return Redirect::back()->withInput()->withErrors('未找到需要编辑的审计规则分组！');
			}

			$data = [];
			if($ruleGroup->name != $name){
				$data['name'] = $name;
			}
			if($ruleGroup->type != $type){
				$data['type'] = $type;
			}
			if($rules){
				$ruleStr = implode(',', $rules);
				if($ruleGroup->rules != $ruleStr){
					$data['rules'] = $ruleStr;
				}else{
					return Redirect::back()->with('successMsg', '检测为未修改，无变动！');
				}
			}elseif(isset($ruleGroup->rules)){
				$data['rules'] = $rules;
			}
			$ret = RuleGroup::query()->whereId($id)->update($data);
			if($ret){
				return Redirect::back()->with('successMsg', '操作成功');
			}
			return Redirect::back()->withInput()->withErrors('操作失败');

		}else{
			$ruleGroup = RuleGroup::query()->find($id);
			if(!$ruleGroup){
				return Redirect::back();
			}
			$view['ruleList'] = Rule::query()->get();

			return view('admin.rule.ruleGroupInfo', $view)->with(compact('ruleGroup'));
		}
	}

	// 删除审计规则分组
	public function delRuleGroup(Request $request) {
		$id = $request->input('id');
		$ruleGroup = RuleGroup::query()->whereId($id)->get();
		if(!$ruleGroup){
			return Response::json(['status' => 'fail', 'message' => '删除失败，未找到审计规则分组']);
		}
		try{
			RuleGroup::query()->whereId($id)->delete();
			RuleGroupNode::query()->whereRuleGroupId($id)->delete();

		}catch(Exception $e){
			return Response::json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
		}
		return Response::json(['status' => 'success', 'message' => '清理成功']);
	}

	// 规则分组关联节点
	public function assignNode(Request $request) {
		$id = $request->input('id');
		if($request->isMethod('POST')){
			$nodes = $request->input('nodes');
			$validator = Validator::make($request->all(), [
				'id' => 'required',
			]);

			if($validator->fails()){
				return Redirect::back()->withInput()->withErrors($validator->errors());
			}

			$ruleGroup = RuleGroup::query()->find($id);
			if(!$ruleGroup){
				return Redirect::back()->withInput()->withErrors('未找到审计规则分组！');
			}

			try{
				if($nodes){
					$nodeStr = implode(',', $nodes);
					// 无变动 不改动
					if($ruleGroup->nodes == $nodeStr){
						return Redirect::back()->with('successMsg', '检测为未修改，无变动！');
					}
					RuleGroup::query()->whereId($id)->update(['nodes' => $nodeStr]);
					RuleGroupNode::query()->whereRuleGroupId($id)->delete();

					foreach($nodes as $nodeId){
						$obj = new RuleGroupNode();
						$obj->rule_group_id = $id;
						$obj->node_id = $nodeId;
						$obj->save();
					}
				}else{
					RuleGroup::query()->whereId($id)->update(['nodes' => $nodes]);
					RuleGroupNode::query()->whereRuleGroupId($id)->delete();
				}

			}catch(Exception $e){
				return Redirect::back()->withInput()->withErrors($e->getMessage());
			}
			return Redirect::back()->with('successMsg', '操作成功');

		}else{
			$view['ruleGroup'] = RuleGroup::query()->find($id);
			$view['nodeList'] = SsNode::query()->get();

			return Response::view('admin.rule.assignNode', $view);
		}
	}

	// 用户触发审计规则日志
	public function ruleLogList(Request $request) {
		$uid = $request->input('uid');
		$email = $request->input('email');
		$nodeId = $request->input('node_id');
		$ruleId = $request->input('rule_id');
		$query = RuleLog::query();

		if($uid){
			$query->whereUserId($uid);
		}
		if(isset($email)){
			$query->whereHas('user', function($q) use ($email) {
				$q->where('email', 'like', '%'.$email.'%');
			});
		}
		if($nodeId){
			$query->whereNodeId($nodeId);
		}
		if($ruleId){
			$query->whereRuleId($ruleId);
		}

		$view['nodeList'] = SsNode::query()->get();
		$view['ruleList'] = Rule::query()->get();
		$view['ruleLogs'] = $query->paginate(15)->appends($request->except('page'));
		return Response::view('admin.rule.ruleLogList', $view);
	}

	// 清除所有审计触发日志
	public function clearLog() {
		try{
			$ret = RuleLog::query()->delete();
		}catch(Exception $e){
			return Response::json(['status' => 'fail', 'message' => '清理失败, '.$e->getMessage()]);
		}
		$result = RuleLog::query()->doesntExist();
		if($ret || $result){
			return Response::json(['status' => 'success', 'message' => '清理成功']);
		}else{
			return Response::json(['status' => 'fail', 'message' => '清理失败']);
		}
	}
}
