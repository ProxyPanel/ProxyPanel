<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RuleRequest;
use App\Models\Node;
use App\Models\Rule;
use App\Models\RuleLog;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class RuleController extends Controller
{
    public function index(Request $request): View
    { // 审计规则列表
        $query = Rule::query();

        $request->whenFilled('type', function ($value) use ($query) {
            $query->whereType($value);
        });

        return view('admin.rule.index', ['rules' => $query->paginate(15)->appends($request->except('page'))]);
    }

    public function store(RuleRequest $request): JsonResponse
    { // 添加审计规则
        try {
            if (Rule::create($request->validated())) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('model.rule.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')])]);
    }

    public function update(RuleRequest $request, Rule $rule): JsonResponse
    { // 编辑审计规则
        try {
            if ($rule->update($request->validated())) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.edit')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.edit'), 'attribute' => trans('model.rule.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')])]);
    }

    public function destroy(Rule $rule): JsonResponse
    { // 删除审计规则
        try {
            if ($rule->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.rule.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }

    public function ruleLogList(Request $request): View
    { // 用户触发审计规则日志
        $query = RuleLog::with(['node:id,name', 'user:id,username', 'rule:id,name']);

        foreach (['user_id', 'node_id', 'rule_id'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        return view('admin.rule.log', [
            'nodes' => Node::pluck('name', 'id'),
            'rules' => Rule::pluck('name', 'id'),
            'ruleLogs' => $query->latest()->paginate(15)->appends($request->except('page')),
        ]);
    }

    // 清除所有审计触发日志
    public function clearLog(): JsonResponse
    {
        try {
            $ret = RuleLog::query()->delete();
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.rule.logs')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        if ($ret || RuleLog::doesntExist()) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
