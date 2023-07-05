<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RuleRequest;
use App\Models\Node;
use App\Models\Rule;
use App\Models\RuleLog;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class RuleController extends Controller
{
    // 审计规则列表
    public function index(Request $request)
    {
        $query = Rule::query();

        $request->whenFilled('type', function ($value) use ($query) {
            $query->whereType($value);
        });

        return view('admin.rule.index', ['rules' => $query->paginate(15)->appends($request->except('page'))]);
    }

    // 添加审计规则
    public function store(RuleRequest $request): JsonResponse
    {
        if (Rule::create($request->validated())) {
            return Response::json(['status' => 'success', 'message' => '提交成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 编辑审计规则
    public function update(RuleRequest $request, Rule $rule): JsonResponse
    {
        if ($rule->update($request->validated())) {
            return Response::json(['status' => 'success', 'message' => '操作成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 删除审计规则
    public function destroy(Rule $rule): JsonResponse
    {
        try {
            $rule->delete();
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '操作失败, '.$e->getMessage()]);
        }

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }

    // 用户触发审计规则日志
    public function ruleLogList(Request $request)
    {
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
            'nodes' => Node::all(),
            'rules' => Rule::all(),
            'ruleLogs' => $query->latest()->paginate(15)->appends($request->except('page')),
        ]);
    }

    // 清除所有审计触发日志
    public function clearLog(): JsonResponse
    {
        try {
            $ret = RuleLog::query()->delete();
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '清理失败, '.$e->getMessage()]);
        }

        if ($ret || RuleLog::doesntExist()) {
            return Response::json(['status' => 'success', 'message' => '清理成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '清理失败']);
    }
}
