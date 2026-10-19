<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Helpers\ResponseEnum;
use App\Helpers\WebApiResponse;
use App\Models\Node;
use App\Models\NodeHeartbeat;
use App\Models\NodeOnlineIp;
use App\Models\Rule;
use App\Models\User;
use App\Models\UserDataFlowLog;
use App\Notifications\Custom;
use App\Utils\Helpers;
use App\Utils\NodeTraffic\LockRetry;
use App\Utils\NodeTraffic\OnlineBatch;
use App\Utils\NodeTraffic\TrafficBatch;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Notification;
use Log;
use Throwable;
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

        $row = [[
            'node_id' => $node->id,
            'uptime' => $data['uptime'],
            'load' => implode(' ', [$data['cpu'] / 100, $data['mem'] / 100, $data['disk'] / 100]),
            'log_time' => time(),
        ]];
        $written = 0;

        // 每节点一行原地刷新：心跳只有「最新一条」被消费，append+purge 是纯 churn
        // 只重跑死锁（数据库整笔回滚这条 upsert，重跑同一次上报安全）；1205 第一次就原样抛出，判据见 LockRetry
        LockRetry::run(function () use (&$written, $row): void {
            $written = NodeHeartbeat::upsert($row, ['node_id'], ['uptime', 'load', 'log_time']);
        });

        if ($written > 0) {
            return $this->succeed();
        }

        return $this->failed([400201, '生成节点心跳信息失败']);
    }

    // 上报节点在线IP
    public function setNodeOnline(Request $request, Node $node): JsonResponse
    {
        // 校验只判形状：逐 uid 做 exists 是一条查询校验一行，且任一 uid 失效（账号被删）就会拒收整批在线记录，
        // 而节点上报完就清空本地在线表 —— 被拒的那一分钟等于白丢。存在性交给下面一次 whereIn 查回。
        // report_id 同样要有规则，validated() 只返回有规则的键。
        $validator = Validator::make($request->all(), ['*.uid' => 'required|integer', '*.ip' => 'required|string', '*.report_id' => 'nullable|string|max:64']);

        if ($validator->fails()) {
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, $validator->errors()->all());
        }

        $records = $validator->validated();
        // uid→port 映射整批一次查回，逐行查是 N 条查询
        $ports = User::whereIn('id', array_column($records, 'uid'))->pluck('port', 'id')->all();
        ['rows' => $rows, 'missing' => $missing] = OnlineBatch::build($records, $ports, $node->id, time());

        if ($missing !== []) {
            // 只记前若干个：上报内容来自节点，不能让它把日志写爆
            Log::warning('【在线上报】节点 #'.$node->id.' 上报了不存在的账号，已跳过：'.implode(',', array_slice($missing, 0, 10)).'（共 '.count($missing).' 条）');
        }

        // 认领按整批判：一批共用一个 report_id。节点发送超时后会把同一批并回重投，
        // 已入账的重投只补 ack；逐行 createMany 既慢（易踩节点 5s 超时）又会把重投放大成重复在线行
        $claim = $this->onlineClaimKey($node->id, $records);

        if ($claim !== null && ! cache()->add($claim, 1, Hour)) {
            return $this->succeed();
        }

        try {
            if ($rows !== []) {
                // 只重跑死锁（数据库整笔回滚，重跑同一批安全）；1205 第一次就原样抛出，判据见 LockRetry
                LockRetry::run(fn () => NodeOnlineIp::insert($rows)); // 一次多行 INSERT 生成节点在线IP数据
            }
        } catch (Exception $e) {
            if ($claim !== null) {
                cache()->forget($claim); // 没入账就放开认领，否则重投的那批被当成已记过而永久丢在线记录
            }

            Log::error('【在线上报】节点 #'.$node->id.' 写入失败：'.$e->getMessage());

            return $this->failed([400201, '生成节点在线用户IP信息失败']);
        }

        if ($node->onlineLogs()->create(['online_user' => count($rows), 'log_time' => time()])) { // 生成节点在线人数数据
            return $this->succeed();
        }

        return $this->failed([400201, '生成节点在线情况失败']);
    }

    // 上报用户流量日志
    public function setUserTraffic(Request $request, Node $node): JsonResponse
    {
        // 校验只判形状：逐 uid 做 exists 是一条查询校验一行，且任一 uid 失效（账号被删）就会拒收整批流量；
        // 用户是否存在由后面一次 whereIn 查回。report_id 同样要有规则，validated() 只返回有规则的键。
        $validator = Validator::make($request->all(), ['*.uid' => 'required|integer', '*.upload' => 'required|numeric', '*.download' => 'required|numeric', '*.report_id' => 'nullable|string|max:64']);

        if ($validator->fails()) {
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, $validator->errors()->all());
        }

        $records = $validator->validated();
        $rate = (float) $node->traffic_rate;
        $logTime = time();
        $traffic = TrafficBatch::aggregate($records, $rate);

        // 没账可记的批次必须 ack 而不能回 fail：VNet-SSR 2.2+ 把没收到 success 的整批留着下一轮原样重投，
        // 且要等这批投递成功才取新快照，回 fail 等于把这个节点的流量账冻在最后一次成功值上
        if (empty($traffic)) {
            return $this->succeed();
        }

        // 一次查出真正存在的账号：上报里残留的已删账号只记日志并跳过，不连累同批其他用户的流量
        $existsIds = array_map('intval', User::whereIn('id', array_keys($traffic))->pluck('id')->all());
        if ($missingIds = array_values(array_diff(array_keys($traffic), $existsIds))) {
            // 只记前若干个：上报内容来自节点，不能让它把日志写爆
            Log::warning('【上报流量】节点 #'.$node->id.' 上报了不存在的账号，已跳过：'.implode(',', array_slice($missingIds, 0, 10)).'（共 '.count($missingIds).' 条）');
        }

        $traffic = array_intersect_key($traffic, array_flip($existsIds));

        if (empty($traffic)) {
            return $this->succeed();
        }

        // 认领按整批判：一批共用一个 report_id，而这批的写入是单事务全有或全无，没有「记了一半」的中间态
        $claim = $this->trafficClaimKey($node->id, $records);

        if ($claim !== null && ! cache()->add($claim, 1, Hour)) {
            // 同一批的重投（面板已入账、响应没送达）：只补 ack 让节点把它排空，绝不重复累加
            return $this->succeed();
        }

        try {
            // 只重跑死锁（数据库整笔回滚，重跑同一批安全）；1205 第一次就原样抛出，判据见 LockRetry
            LockRetry::run(fn () => DB::transaction(function () use ($node, $traffic, $rate, $logTime) {
                // 先累加再插记录：插子表会经外键给 user 行加 S 锁，同事务再把 S 升成 X 就是并发上报的死锁源
                foreach (TrafficBatch::chunks($traffic) as $chunk) { // 分块累加到账号上，每块一条 UPDATE
                    $query = TrafficBatch::buildUpdateQuery($chunk, $logTime);
                    DB::update($query['sql'], $query['bindings']);
                }

                UserDataFlowLog::insert(TrafficBatch::buildLogRows($traffic, $node->id, $rate, $logTime)); // 一次 INSERT 写完所有记录
            }));
        } catch (Exception $e) {
            if ($claim !== null) {
                cache()->forget($claim); // 没入账就要放开认领，否则重投的那批会被当成已记过而永久丢账
            }

            Log::error('【上报流量】节点 #'.$node->id.' 写入失败：'.$e->getMessage());

            return $this->failed([400201, '生成用户流量日志失败']);
        }

        return $this->succeed();
    }

    /**
     * 批次认领键；不发 report_id 的上报（SS/SSR/V2Ray/Trojan/Hysteria2）得到 null，逐次累加的语义不变.
     */
    private function trafficClaimKey(int $nodeId, array $records): ?string
    {
        $reportId = reset($records)['report_id'] ?? null;

        return $reportId === null ? null : 'nodeTrafficReport_'.$nodeId.'_'.$reportId;
    }

    /**
     * 在线批次认领键；不发 report_id 的上报得到 null，保持逐次写入的语义不变.
     */
    private function onlineClaimKey(int $nodeId, array $records): ?string
    {
        $reportId = reset($records)['report_id'] ?? null;

        return $reportId === null ? null : 'nodeOnlineReport_'.$nodeId.'_'.$reportId;
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
        // rule_id 允许 0：白名单模式「访问了非规则允许的网址」没有对应规则；FK 只认存在的 id 或 NULL
        $validator = Validator::make($request->all(), [
            'uid' => 'required|numeric', // 存在性在下面是独立一步：账号被删是正常竞态，不是参数错误
            'rule_id' => 'required|numeric|min:0',
            'reason' => 'required',
            'target' => 'nullable|string|max:255',
            'sniff_protocol' => 'nullable|in:http,tls',
            'sniff_host' => 'nullable|string|max:255',
            'sniff_uri' => 'nullable|string|max:1024',
        ]);

        if ($validator->fails()) {
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR, $validator->errors()->all());
        }
        $data = $validator->validated();
        // 账号已经不在了：没有可挂的记录、也没有可封的人。回 fail 只会让节点重投同一条，
        // 「没账可记也要 ack」与流量上报是同一判据
        if (! User::whereKey((int) $data['uid'])->exists()) {
            return $this->succeed();
        }
        // 规则可能已在节点拉取后被删除，FK 会拒这种行；归 NULL 保住触发记录本身
        $ruleId = (int) $data['rule_id'] > 0 && Rule::whereKey($data['rule_id'])->exists() ? (int) $data['rule_id'] : null;

        // 封禁计数与记录解耦：记录有防刷屏冷却，计数看每一次上报（节点侧本就按 (账号,目标) 5 分钟去重）
        $this->enforceBanThreshold((int) $data['uid'], $node);

        // 记录冷却：同一 (node,账号,规则,原因) 在 rule_log_cooldown 分钟内只留一档——
        // 后台软件无感触发的重复违规不该把审计日志刷成噪音
        $claim = 'ruleLog_'.$node->id.'_'.$data['uid'].'_'.$ruleId.'_'.md5((string) $data['reason']);
        if (! cache()->add($claim, 1, now()->addMinutes(max(1, (int) sysConfig('rule_log_cooldown', 30))))) {
            return $this->succeed();
        }

        try {
            $node->ruleLogs()->create([
                'user_id' => $data['uid'],
                'rule_id' => $ruleId,
                'reason' => $data['reason'],
                'target' => $data['target'] ?? null,
                'sniff_protocol' => $data['sniff_protocol'] ?? null,
                'sniff_host' => $data['sniff_host'] ?? null,
                'sniff_uri' => $data['sniff_uri'] ?? null,
            ]);
        } catch (Exception $e) { // 关系上的 create() 只会返回模型或抛异常，判返回值 false 是走不到的分支
            cache()->forget($claim); // 没入账就放开认领，否则重投的那条被当成已记过而永久丢记录

            Log::error('【审计规则】节点 #'.$node->id.' 触发记录写入失败：'.$e->getMessage());

            return $this->failed([400201, '上报用户触发审计规则日志失败']);
        }

        return $this->succeed();
    }

    /**
     * 阈值处置：rule_ban_times=N>0 时，账号每小时（整点桶）触发达到 N 次 → 临时封禁并通知管理员；
     * 0 = 只记录，不封禁也不通知（否则管理员会被无感触发轰炸）。封禁只发生在 enable 1→0 的那一次，
     * 通知与封禁共用同一阈值与同一次触发，天然不重复。
     */
    private function enforceBanThreshold(int $uid, Node $node): void
    {
        $threshold = (int) sysConfig('rule_ban_times');
        if ($threshold <= 0) {
            return;
        }

        $bucket = 'ruleHits_'.$uid.'_'.now()->format('YmdH');
        $hits = cache()->add($bucket, 1, now()->addHour()) ? 1 : (int) cache()->increment($bucket);
        if ($hits < $threshold) {
            return;
        }

        $user = User::find($uid);
        if ($user === null || (int) $user->enable !== 1) {
            return;
        }

        $banDuration = Helpers::banDurationMinutes();
        Helpers::tempBanUser($user, $banDuration, __('[Audit Rule] Blocked service: audit rule triggered repeatedly within 1 hour'));
        Log::notice('【审计规则】账号 #'.$uid.' 一小时内触发 '.$hits.' 次，已临时封禁 '.$banDuration.' 分钟');

        try {
            Notification::send(User::find(1), new Custom(
                trans('notification.rule_ban'),
                trans('notification.rule_ban_content', [
                    'uid' => $uid,
                    'email' => $user->username,
                    'hits' => $hits,
                    'threshold' => $threshold,
                    'minutes' => $banDuration,
                    'node_id' => $node->id,
                    'node_name' => $node->name,
                ])
            ));
        } catch (Throwable $e) {
            Log::error('【审计规则】封禁提醒发送失败：'.$e->getMessage()); // 通知失败不该把已封禁的上报打成 fail
        }
    }
}
