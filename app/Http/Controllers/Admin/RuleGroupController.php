<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\Rule;
use App\Models\RuleGroup;
use App\Models\RuleGroupNode;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Validator;

class RuleGroupController extends Controller
{
    // 审计规则分组列表
    public function index(Request $request)
    {
        $view['ruleGroupList'] = RuleGroup::paginate(15)->appends($request->except('page'));

        return view('admin.rule.group.index', $view);
    }

    // 添加审计规则分组页面
    public function create()
    {
        $view['ruleList'] = Rule::all();

        return view('admin.rule.group.info', $view);
    }

    // 添加审计规则分组
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'type'  => 'required|boolean',
            'rules' => 'required',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator->errors());
        }

        $obj = new RuleGroup();
        $obj->name = $request->input('name');
        $obj->type = (int) $request->input('type');
        $obj->rules = $request->input('rules');
        $obj->save();

        if ($obj->id) {
            return Redirect::back()->with('successMsg', '操作成功');
        }

        return Redirect::back()->withInput()->withErrors('操作失败');
    }

    // 编辑审计规则分组页面
    public function edit($id)
    {
        $view['ruleGroup'] = RuleGroup::find($id);
        $view['ruleList'] = Rule::all();

        return view('admin.rule.group.info', $view);
    }

    // 编辑审计规则分组
    public function update(Request $request, $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator->errors());
        }

        $ret = RuleGroup::find($id)->update([
            'name'  => $request->input('name'),
            'type'  => $request->input('type'),
            'rules' => $request->input('rules'),
        ]);

        if ($ret) {
            return Redirect::back()->with('successMsg', '操作成功');
        }

        return Redirect::back()->withInput()->withErrors('操作失败');
    }

    // 删除审计规则分组
    public function destroy($id): JsonResponse
    {
        try {
            RuleGroup::whereId($id)->delete();
            RuleGroupNode::whereRuleGroupId($id)->delete();
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
        }

        return Response::json(['status' => 'success', 'message' => '清理成功']);
    }

    // 规则分组关联节点
    public function assignNode($id)
    {
        $view['ruleGroup'] = RuleGroup::find($id);
        $view['nodeList'] = Node::all();

        return view('admin.rule.group.assign', $view);
    }

    // 规则分组关联节点
    public function assign(Request $request, $id)
    {
        $nodes = $request->input('nodes');
        $ruleGroup = RuleGroup::findOrFail($id);

        try {
            if ($ruleGroup->nodes === $nodes) {
                return Redirect::back()->with('successMsg', '检测为未修改，无变动！');
            }
            RuleGroupNode::whereRuleGroupId($id)->delete();
            if ($nodes) {
                $ruleGroup->nodes = $nodes;
                if (! $ruleGroup->save()) {
                    return Redirect::back()->withErrors('更新错误！');
                }

                foreach ($nodes as $nodeId) {
                    $obj = new RuleGroupNode();
                    $obj->rule_group_id = $id;
                    $obj->node_id = $nodeId;
                    $obj->save();
                }
            } else {
                RuleGroup::find($id)->update(['nodes' => null]);
            }
        } catch (Exception $e) {
            return Redirect::back()->withInput()->withErrors($e->getMessage());
        }

        return Redirect::back()->with('successMsg', '操作成功');
    }
}
