<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RuleRequest;
use App\Models\Node;
use App\Models\Rule;
use App\Models\RuleLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    use ActionResponse;

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
        return $this->actionResponse('common.add', 'model.rule.attribute', fn () => Rule::create($request->validated()));
    }

    public function update(RuleRequest $request, Rule $rule): JsonResponse
    { // 编辑审计规则
        return $this->actionResponse('common.edit', 'model.rule.attribute', fn () => $rule->update($request->validated()));
    }

    public function destroy(Rule $rule): JsonResponse
    { // 删除审计规则
        return $this->actionResponse('common.delete', 'model.rule.attribute', fn () => $rule->delete());
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
        return $this->actionResponse('common.delete', 'model.rule.logs', fn () => RuleLog::query()->delete() || RuleLog::doesntExist());
    }
}
