<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Models\Node;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

class BaseController
{
    // 上报节点心跳信息
    public function setNodeStatus(Request $request, Node $node): JsonResponse
    {
        $validator = Validator::make($request->all(), ['cpu' => 'required', 'mem' => 'required', 'disk' => 'required', 'uptime' => 'required|numeric']);

        if ($validator->fails()) {
            return $this->returnData('上报节点心跳信息失败，请检查字段');
        }

        $data = array_map('intval', $validator->validated());

        if ($node->heartbeats()->create([
            'uptime' => $data['uptime'],
            'load' => implode(' ', [$data['cpu'] / 100, $data['mem'] / 100, $data['disk'] / 100]),
            'log_time' => time(),
        ])) {
            return $this->returnData('上报节点心跳信息成功', 'success', 200);
        }

        return $this->returnData('生成节点心跳信息失败');
    }

    // 返回数据
    public function returnData(string $message, string $status = 'fail', int $code = 400, array $data = [], array $addition = null): JsonResponse
    {
        $etag = self::abortIfNotModified($data);
        $data = ['status' => $status, 'code' => $code, 'data' => $data, 'message' => $message];

        if (isset($addition)) {
            $data = array_merge($data, $addition);
        }

        return Response::json($data)->header('ETAG', $etag)->setStatusCode($code);
    }

    // 检查数据是否有变动
    private static function abortIfNotModified($data): string
    {
        $req = request();
        // Only for "GET" method
        if (! $req->isMethod('GET')) {
            return '';
        }

        $etag = sha1(json_encode($data));
        if ($etag === $req->header('IF-NONE-MATCH')) {
            abort(304);
        }

        return $etag;
    }

    // 上报节点在线IP
    public function setNodeOnline(Request $request, Node $node): JsonResponse
    {
        $validator = Validator::make($request->all(), ['*.uid' => 'required|numeric|exists:user,id', '*.ip' => 'required|string']);

        if ($validator->fails()) {
            return $this->returnData('上报节点在线用户IP信息失败，请检查字段');
        }

        $onlineCount = 0;
        foreach ($validator->validated() as $input) { // 处理节点在线IP数据
            $formattedData[] = ['user_id' => $input['uid'], 'ip' => $input['ip'], 'port' => User::find($input['uid'])->port, 'created_at' => time()];
            $onlineCount++;
        }

        if (isset($formattedData) && ! $node->onlineIps()->createMany($formattedData)) {  // 生成节点在线IP数据
            return $this->returnData('生成节点在线用户IP信息失败');
        }

        if ($node->onlineLogs()->create(['online_user' => $onlineCount, 'log_time' => time()])) { // 生成节点在线人数数据
            return $this->returnData('上报节点在线情况成功', 'success', 200);
        }

        return $this->returnData('生成节点在线情况失败');
    }

    // 上报用户流量日志
    public function setUserTraffic(Request $request, Node $node): JsonResponse
    {
        $validator = Validator::make($request->all(), ['*.uid' => 'required|numeric|exists:user,id', '*.upload' => 'required|numeric', '*.download' => 'required|numeric']);

        if ($validator->fails()) {
            return $this->returnData('上报用户流量日志失败，请检查字段');
        }

        foreach ($validator->validated() as $input) { // 处理用户流量数据
            $rate = $node->traffic_rate;
            $u = $input['upload'] * $rate;
            $d = $input['download'] * $rate;

            $formattedData[] = ['user_id' => $input['uid'], 'u' => $u, 'd' => $d, 'rate' => $rate, 'traffic' => flowAutoShow($u + $d), 'log_time' => time()];
        }

        if (isset($formattedData) && $logs = $node->userDataFlowLogs()->createMany($formattedData)) { // 生成用户流量数据
            foreach ($logs as $log) { // 更新用户流量数据
                $user = $log->user;
                $user->update(['u' => $user->u + $log->u, 'd' => $user->d + $log->d, 't' => time()]);
            }

            return $this->returnData('上报用户流量日志成功', 'success', 200);
        }

        return $this->returnData('生成用户流量日志失败');
    }

    // 获取节点的审计规则
    public function getNodeRule(Node $node): JsonResponse
    {
        // 节点未设置任何审计规则
        if ($ruleGroup = $node->ruleGroup) {
            foreach ($ruleGroup->rules as $rule) {
                $data[] = [
                    'id' => $rule->id,
                    'type' => $rule->type_api_label,
                    'pattern' => $rule->pattern,
                ];
            }

            return $this->returnData('获取节点审计规则成功', 'success', 200, ['mode' => $ruleGroup->type ? 'reject' : 'allow', 'rules' => $data ?? []]);
        }

        // 放行
        return $this->returnData('获取节点审计规则成功', 'success', 200, ['mode' => 'all', 'rules' => $data ?? []]);
    }

    // 上报用户触发审计规则记录
    public function addRuleLog(Request $request, Node $node): JsonResponse
    {
        $validator = Validator::make($request->all(), ['uid' => 'required|numeric|exists:user,id', 'rule_id' => 'required|numeric|exists:rule,id', 'reason' => 'required']);

        if ($validator->fails()) {
            return $this->returnData('上报用户触发审计规则日志失败，请检查字段');
        }
        $data = $validator->validated();
        if ($node->ruleLogs()->create(['user_id' => $data['uid'], 'rule_id' => $data['rule_id'], 'reason' => $data['reason']])) {
            return $this->returnData('上报用户触发审计规则日志成功', 'success', 200);
        }

        return $this->returnData('上报用户触发审计规则日志失败');
    }
}
