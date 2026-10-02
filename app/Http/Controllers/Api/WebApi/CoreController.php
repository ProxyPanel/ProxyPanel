<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Helpers\ResponseEnum;
use App\Helpers\WebApiResponse;
use App\Models\Node;
use App\Models\User;
use App\Models\UserDataFlowLog;
use App\Utils\NodeTraffic\TrafficBatch;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Log;
use Validator;

class CoreController extends Controller
{
    use WebApiResponse;

    // 上报节点心跳信息
    public function setNodeStatus(Request $request, Node $node): JsonResponse
    {
        $validator = Validator::make($request->all(), ['cpu' => 'required', 'mem' => 'required', 'disk' => 'required', 'uptime' => 'required|numeric']);

        if ($validator->fails()) {
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, $validator->errors()->all());
        }

        $data = array_map('intval', $validator->validated());

        if ($node->heartbeats()->create([
            'uptime' => $data['uptime'],
            'load' => implode(' ', [$data['cpu'] / 100, $data['mem'] / 100, $data['disk'] / 100]),
            'log_time' => time(),
        ])) {
            return $this->succeed();
        }

        return $this->failed([400201, '生成节点心跳信息失败']);
    }

    // 上报节点在线IP
    public function setNodeOnline(Request $request, Node $node): JsonResponse
    {
        $validator = Validator::make($request->all(), ['*.uid' => 'required|numeric|exists:user,id', '*.ip' => 'required|string']);

        if ($validator->fails()) {
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, $validator->errors()->all());
        }

        $onlineCount = 0;
        foreach ($validator->validated() as $input) { // 处理节点在线IP数据
            $formattedData[] = ['user_id' => $input['uid'], 'ip' => $input['ip'], 'port' => User::find($input['uid'])->port, 'created_at' => time()];
            $onlineCount++;
        }

        if (isset($formattedData) && ! $node->onlineIps()->createMany($formattedData)) {  // 生成节点在线IP数据
            return $this->failed([400201, '生成节点在线用户IP信息失败']);
        }

        if ($node->onlineLogs()->create(['online_user' => $onlineCount, 'log_time' => time()])) { // 生成节点在线人数数据
            return $this->succeed();
        }

        return $this->failed([400201, '生成节点在线情况失败']);
    }

    // 上报用户流量日志
    public function setUserTraffic(Request $request, Node $node): JsonResponse
    {
        // 不再对每个 uid 做 exists 校验：那是一条查询校验一行，而且只要有一个 uid 已失效（账号被删），
        // 整批流量都会被拒收。这里只校验形状，用户是否存在改成一次 whereIn 查回来。
        $validator = Validator::make($request->all(), ['*.uid' => 'required|integer', '*.upload' => 'required|numeric', '*.download' => 'required|numeric']);

        if ($validator->fails()) {
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, $validator->errors()->all());
        }

        $rate = (float) $node->traffic_rate;
        $logTime = time();
        $traffic = TrafficBatch::aggregate($validator->validated(), $rate);

        if (empty($traffic)) {
            return $this->failed([400201, '生成用户流量日志失败']);
        }

        // 一次查出真正存在的账号：上报里残留的已删账号只记日志并跳过，不连累同批其他用户的流量
        $existsIds = array_map('intval', User::whereIn('id', array_keys($traffic))->pluck('id')->all());
        if ($missingIds = array_values(array_diff(array_keys($traffic), $existsIds))) {
            // 只记前若干个：上报内容来自节点，不能让它把日志写爆
            Log::warning('【上报流量】节点 #'.$node->id.' 上报了不存在的账号，已跳过：'.implode(',', array_slice($missingIds, 0, 10)).'（共 '.count($missingIds).' 条）');
        }

        $traffic = array_intersect_key($traffic, array_flip($existsIds));

        if (empty($traffic)) {
            return $this->failed([400201, '生成用户流量日志失败']);
        }

        try {
            DB::transaction(function () use ($node, $traffic, $rate, $logTime) {
                // 一次 INSERT 写完记录：createMany() 内部是逐行 INSERT
                UserDataFlowLog::insert(TrafficBatch::buildLogRows($traffic, $node->id, $rate, $logTime));

                // 分块累加到账号上，每块一条 UPDATE
                foreach (TrafficBatch::chunks($traffic) as $chunk) {
                    $query = TrafficBatch::buildUpdateQuery($chunk, $logTime);
                    DB::update($query['sql'], $query['bindings']);
                }
            });
        } catch (Exception $e) {
            Log::error('【上报流量】节点 #'.$node->id.' 写入失败：'.$e->getMessage());

            return $this->failed([400201, '生成用户流量日志失败']);
        }

        return $this->succeed();
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

            return $this->succeed(['mode' => $ruleGroup->type ? 'reject' : 'allow', 'rules' => $data ?? []]);
        }

        // 放行
        return $this->succeed(['mode' => 'all', 'rules' => $data ?? []]);
    }

    // 上报用户触发审计规则记录
    public function addRuleLog(Request $request, Node $node): JsonResponse
    {
        $validator = Validator::make($request->all(), ['uid' => 'required|numeric|exists:user,id', 'rule_id' => 'required|numeric|exists:rule,id', 'reason' => 'required']);

        if ($validator->fails()) {
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, $validator->errors()->all());
        }
        $data = $validator->validated();
        if ($node->ruleLogs()->create(['user_id' => $data['uid'], 'rule_id' => $data['rule_id'], 'reason' => $data['reason']])) {
            return $this->succeed();
        }

        return $this->failed([400201, '上报用户触发审计规则日志失败']);
    }
}
