<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\Rule;
use App\Models\RuleGroup;
use App\Models\RuleLog;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

class RuleController extends Controller
{
    // 审计规则列表
    public function index(Request $request)
    {
        $type = $request->input('type');
        $query = Rule::query();

        if ($type) {
            $query->whereType($type);
        }

        $view['rules'] = $query->paginate(15)->appends($request->except('page'));

        return view('admin.rule.index', $view);
    }

    // 添加审计规则
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type'    => 'required|between:1,4',
            'name'    => 'required',
            'pattern' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        $rule = new Rule();
        $rule->type = $request->input('type');
        $rule->name = $request->input('name');
        $rule->pattern = $request->input('pattern');
        $rule->save();

        if ($rule->id) {
            return Response::json(['status' => 'success', 'message' => '提交成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 编辑审计规则
    public function update(Request $request, $id): JsonResponse
    {
        if (Rule::find($id)->update(['name' => $request->input('rule_name'), 'pattern' => $request->input('rule_pattern')])) {
            return Response::json(['status' => 'success', 'message' => '操作成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 删除审计规则
    public function destroy($id): JsonResponse
    {
        try {
            Rule::whereId($id)->delete();

            foreach (RuleGroup::all() as $ruleGroup) {
                $rules = $ruleGroup->rules;
                if ($rules && in_array($id, $rules, true)) {
                    $ruleGroup->rules = array_merge(array_diff($rules, [$id]));
                    $ruleGroup->save();
                }
            }
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '操作失败, '.$e->getMessage()]);
        }

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }

    // 用户触发审计规则日志
    public function ruleLogList(Request $request)
    {
        $uid = $request->input('uid');
        $email = $request->input('email');
        $nodeId = $request->input('node_id');
        $ruleId = $request->input('rule_id');
        $query = RuleLog::query();

        if ($uid) {
            $query->whereUserId($uid);
        }
        if (isset($email)) {
            $query->whereHas('user', static function ($q) use ($email) {
                $q->where('email', 'like', '%'.$email.'%');
            });
        }
        if ($nodeId) {
            $query->whereNodeId($nodeId);
        }
        if ($ruleId) {
            $query->whereRuleId($ruleId);
        }

        $view['nodeList'] = Node::all();
        $view['ruleList'] = Rule::all();
        $view['ruleLogs'] = $query->latest()->paginate(15)->appends($request->except('page'));

        return view('admin.rule.log', $view);
    }

    // 清除所有审计触发日志
    public function clearLog(): JsonResponse
    {
        try {
            $ret = RuleLog::query()->delete();
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '清理失败, '.$e->getMessage()]);
        }
        $result = RuleLog::doesntExist();
        if ($ret || $result) {
            return Response::json(['status' => 'success', 'message' => '清理成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '清理失败']);
    }
}
