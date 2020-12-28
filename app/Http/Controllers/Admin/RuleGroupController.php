<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RuleGroupRequest;
use App\Models\Rule;
use App\Models\RuleGroup;
use Exception;

class RuleGroupController extends Controller
{
    // 审计规则分组列表
    public function index()
    {
        return view('admin.rule.group.index', ['ruleGroups' => RuleGroup::paginate(15)->appends(request('page'))]);
    }

    // 添加审计规则分组页面
    public function create()
    {
        return view('admin.rule.group.info', ['rules' => Rule::all()]);
    }

    // 添加审计规则分组
    public function store(RuleGroupRequest $request)
    {
        if ($group = RuleGroup::create($request->only('name', 'type'))) {
            $group->rules()->attach($request->input('rules'));

            return redirect(route('admin.rule.group.edit', $group))->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    // 编辑审计规则分组页面
    public function edit(RuleGroup $group)
    {
        return view('admin.rule.group.info', [
            'ruleGroup' => $group,
            'rules' => Rule::all(),
        ]);
    }

    // 编辑审计规则分组
    public function update(RuleGroupRequest $request, RuleGroup $group)
    {
        if ($group->update($request->only(['name', 'type']))) {
            $group->rules()->sync($request->input('rules'));

            return redirect()->back()->with('successMsg', '操作成功');
        }

        return redirect()->back()->withInput()->withErrors('操作失败');
    }

    // 删除审计规则分组
    public function destroy(RuleGroup $group)
    {
        try {
            $group->delete();
        } catch (Exception $e) {
            return response()->json(['status' => 'fail', 'message' => '删除失败，'.$e->getMessage()]);
        }

        return response()->json(['status' => 'success', 'message' => '清理成功']);
    }
}
