<?php

namespace App\Http\Controllers\Admin;

use App\Events\NodeActions;
use App\Helpers\DataChart;
use App\Helpers\ProxyConfig;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NodeRequest;
use App\Jobs\VNet\ReloadNode;
use App\Models\Country;
use App\Models\Label;
use App\Models\Level;
use App\Models\Node;
use App\Models\NodeCertificate;
use App\Models\RuleGroup;
use App\Utils\NetworkDetection;
use Arr;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;

class NodeController extends Controller
{
    use DataChart, ProxyConfig;

    public function index(Request $request): View
    { // 节点列表
        $query = Node::whereNull('relay_node_id')
            ->with([
                'dailyDataFlows' => function ($query) {
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()]);
                },
                'hourlyDataFlows' => function ($query) {
                    $query->whereDate('created_at', now()->toDateString());
                },
                'latestOnlineLog',
                'latestHeartbeat',
                'childNodes',
            ]);

        $request->whenFilled('status', function ($value) use ($query) {
            $query->where('status', $value);
        });

        $nodeList = $query->orderByDesc('sort')->orderBy('id')->paginate(15)->appends($request->except('page'))->through(function ($node) {
            // 预处理每个节点的数据
            $node->online_users = $node->latestOnlineLog?->online_user; // 在线人数

            // 计算流量总和
            $dailyTransfer = $node->dailyDataFlows->sum(fn ($item) => $item->u + $item->d);
            $hourlyTransfer = $node->hourlyDataFlows->sum(fn ($item) => $item->u + $item->d);
            $node->transfer = formatBytes($dailyTransfer + $hourlyTransfer); // 已产生流量

            $node_info = $node->latestHeartbeat; // 近期负载
            $node->isOnline = ! empty($node_info?->load);
            $node->load = $node_info?->load ?? false;
            $node->uptime = formatTime($node_info?->uptime);

            return $node;
        });

        return view('admin.node.index', compact('nodeList'));
    }

    public function store(NodeRequest $request): JsonResponse
    { // 添加节点
        // 获取验证后的数据
        $validatedData = $request->validated();

        // 构建操作清单
        $operationList = ['save_node_info', 'create_auth', 'sync_labels', 'refresh_geo'];

        // 根据节点配置添加相应的操作项
        if (! ($validatedData['is_ddns'] ?? false) && ($validatedData['server'] ?? false) && sysConfig('ddns_mode')) {
            $operationList[] = 'handle_ddns';
        }

        // 发送操作清单
        broadcast(new NodeActions('create', ['list' => $operationList]));

        try {
            // 保存节点信息
            if ($node = Node::create($this->nodeStore($validatedData))) {
                broadcast(new NodeActions('create', ['operation' => 'save_node_info', 'status' => 1]));
                if ($request->has('labels')) { // 生成节点标签
                    $node->labels()->attach($request->input('labels'));
                }
                broadcast(new NodeActions('create', ['operation' => 'sync_labels', 'status' => 1]));

                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('model.node.attribute')]).': '.$e->getMessage());
            broadcast(new NodeActions('create', ['status' => 0, 'message' => $e->getMessage()]));

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')]).', '.$e->getMessage()]);
        }
        broadcast(new NodeActions('create', ['status' => 0]));

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')])]);
    }

    public function create(): View
    {
        return view('admin.node.info', [
            'nodes' => Node::orderBy('id')->pluck('id', 'name'),
            'countries' => Country::orderBy('code')->get(),
            'levels' => Level::orderBy('level')->pluck('name', 'level'),
            'ruleGroups' => RuleGroup::orderBy('id')->pluck('name', 'id'),
            'labels' => Label::orderByDesc('sort')->orderBy('id')->pluck('name', 'id'),
            'certs' => NodeCertificate::orderBy('id')->pluck('domain', 'id'),
            ...$this->proxyConfigOptions(),
        ]);
    }

    private function nodeStore(array $info): array
    { // 添加节点信息
        switch ($info['type']) {
            case 0:
                $profile = ['method' => $info['method']];
                break;
            case 2:
                $profile = [
                    'method' => $info['v2_method'],
                    'v2_alter_id' => $info['v2_alter_id'],
                    'v2_net' => $info['v2_net'],
                    'v2_type' => $info['v2_type'],
                    'v2_host' => $info['v2_host'],
                    'v2_path' => $info['v2_path'],
                    'v2_tls' => $info['v2_tls'] ? 'tls' : '',
                    'v2_sni' => $info['v2_sni'],
                ];
                break;
            case 3:
                $profile = [
                    'allow_insecure' => false,
                ];
                break;
            case 1:
            case 4:
                $profile = [
                    'method' => $info['method'],
                    'protocol' => $info['protocol'],
                    'obfs' => $info['obfs'],
                    'obfs_param' => $info['obfs_param'],
                    'protocol_param' => $info['protocol_param'],
                    'passwd' => $info['passwd'],
                ];
                break;
        }

        $details = [
            'next_renewal_date' => $info['next_renewal_date'],
            'subscription_term' => $info['subscription_term'],
            'renewal_cost' => $info['renewal_cost'],
        ];

        array_clean($details);

        return [
            'type' => $info['type'],
            'name' => $info['name'],
            'country_code' => $info['country_code'],
            'server' => $info['server'],
            'ip' => $info['ip'],
            'ipv6' => $info['ipv6'],
            'level' => $info['level'],
            'rule_group_id' => $info['rule_group_id'],
            'speed_limit' => $info['speed_limit'],
            'client_limit' => $info['client_limit'],
            'details' => ! empty($details) ? $details : null,
            'description' => $info['description'],
            'profile' => $profile ?? [],
            'traffic_rate' => $info['traffic_rate'],
            'is_udp' => $info['is_udp'],
            'is_display' => $info['is_display'],
            'is_ddns' => $info['is_ddns'],
            'relay_node_id' => $info['relay_node_id'],
            'port' => $info['port'] ?? 0,
            'push_port' => $info['push_port'],
            'detection_type' => $info['detection_type'],
            'sort' => $info['sort'],
            'status' => $info['status'],
        ];
    }

    public function clone(Node $node): RedirectResponse
    { // 克隆节点
        $clone = [
            'name' => $node->name.'_'.trans('admin.clone'),
            'server' => null,
        ];

        if ($node->is_ddns) {
            $clone['ip'] = '1.1.1.1';
            $clone['is_ddns'] = 0;
        }

        $new = $node->replicate()->fill($clone);
        $new->save();

        return redirect(route('admin.node.edit', $new));
    }

    public function edit(Node $node): View
    { // 编辑节点页面
        $node->load('labels:id');
        $nodeArray = $node->toArray();

        return view('admin.node.info', [
            'node' => array_merge(
                Arr::except($nodeArray, ['details', 'profile']),
                $nodeArray['details'] ?? [],
                $nodeArray['profile'] ?? [],
                ['labels' => $node->labels->pluck('id')->toArray()]// 将标签ID列表作为一维数组
            ),
            'nodes' => Node::whereNotIn('id', [$node->id])->orderBy('id')->pluck('id', 'name'),
            'countries' => Country::orderBy('code')->get(),
            'levels' => Level::orderBy('level')->pluck('name', 'level'),
            'ruleGroups' => RuleGroup::orderBy('id')->pluck('name', 'id'),
            'labels' => Label::orderByDesc('sort')->orderBy('id')->pluck('name', 'id'),
            'certs' => NodeCertificate::orderBy('id')->pluck('domain', 'id'),
            ...$this->proxyConfigOptions(),
        ]);
    }

    public function update(NodeRequest $request, Node $node): JsonResponse
    { // 编辑节点
        // 获取验证后的数据
        $validatedData = $request->validated();

        // 构建操作清单
        $operationList = ['save_node_info', 'sync_labels', 'refresh_geo']; // 操作清单

        if (! ($validatedData['is_ddns'] ?? $node->is_ddns) && ($validatedData['server'] ?? $node->server) && sysConfig('ddns_mode')) { // 检查是否有DDNS相关变更
            $operationList[] = 'handle_ddns';
        }

        if ((int) ($validatedData['type'] ?? $node->type) === 4) { // 检查是否是VNET节点（可能需要重新加载）
            $operationList[] = 'reload_node';
        }

        // 发送操作清单
        broadcast(new NodeActions('update', ['list' => $operationList], $node->id));

        try {
            // 先尝试更新节点信息
            if ($node->update($this->nodeStore($validatedData))) {
                broadcast(new NodeActions('update', ['operation' => 'save_node_info', 'status' => 1], $node->id));

                // 如果没有字段变更，强制触发更新以确保 observer 被调用
                if (empty($node->getChanges())) {
                    $node->touch();
                }

                // 同步节点标签
                $node->labels()->sync($request->input('labels'));
                broadcast(new NodeActions('update', ['operation' => 'sync_labels', 'status' => 1], $node->id));

                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.edit')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.edit'), 'attribute' => trans('model.node.attribute')]).': '.$e->getMessage());
            broadcast(new NodeActions('update', ['status' => 0, 'message' => $e->getMessage()], $node->id));

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')]).', '.$e->getMessage()]);
        }
        broadcast(new NodeActions('update', ['status' => 0], $node->id));

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')])]);
    }

    public function destroy(Node $node): JsonResponse
    { // 删除节点
        // 发送操作清单给前端
        $operationList = ['delete_node'];

        // 根据节点配置添加相应的操作项
        if ($node->server && sysConfig('ddns_mode')) {
            $operationList[] = 'handle_ddns';
        }

        broadcast(new NodeActions('delete', ['list' => $operationList], $node->id));

        try {
            // 删除节点
            if ($node->delete()) {
                broadcast(new NodeActions('delete', ['operation' => 'delete_node', 'status' => 1], $node->id));

                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.node.attribute')]).': '.$e->getMessage());
            broadcast(new NodeActions('delete', ['status' => 0, 'message' => $e->getMessage()], $node->id));

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        broadcast(new NodeActions('delete', ['status' => 0], $node->id));

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }

    public function checkNode(?Node $node = null): JsonResponse
    {
        // 获取节点集合并预加载IP信息
        $fields = ['id', 'name', 'is_ddns', 'server', 'ip', 'ipv6', 'port'];
        $nodes = ($node ? collect([$node]) : Node::whereStatus(1)->select($fields)->get())->map(function ($n) {
            return ['node' => $n, 'ips' => $n->ips()];
        });

        // 构建节点列表信息
        $nodeList = $nodes->mapWithKeys(function ($item) {
            return [$item['node']->id => ['name' => $item['node']->name, 'ips' => $item['ips']]];
        })->toArray();

        // 立即发送节点列表信息给前端
        broadcast(new NodeActions('check', ['list' => $nodeList], $node?->id));

        // 异步分发检测任务，提高响应速度
        $nodes->each(function ($item) use ($node) {
            dispatch(static function () use ($item, $node) {
                foreach ($item['ips'] as $ip) {
                    $ret = ['ip' => $ip, 'icmp' => 4, 'tcp' => 4, 'node_id' => $item['node']->id, 'status' => 1];
                    try {
                        $status = NetworkDetection::networkStatus($ip, $item['node']->port ?? 22);
                        $ret['icmp'] = $status['icmp'];
                        $ret['tcp'] = $status['tcp'];
                    } catch (Exception $e) {
                        Log::error("节点 [{$item['node']->id}] IP [$ip] 检测失败: ".$e->getMessage());
                        $ret += ['message' => $e->getMessage()];
                        $ret['status'] = 0;
                    }

                    broadcast(new NodeActions('check', $ret, $node?->id));
                }
            });
        });

        return response()->json([
            'status' => 'success',
            'message' => trans('common.success_item', [
                'attribute' => $node ? trans('admin.node.connection_test') : trans('admin.node.connection_test_all'),
            ]),
        ]);
    }

    public function refreshGeo(?Node $node = null): JsonResponse
    {
        $nodes = $node ? collect([$node]) : Node::whereStatus(1)->get();

        // 发送节点列表信息
        broadcast(new NodeActions('geo', ['list' => $nodes->pluck('name', 'id')], $node?->id));

        // 异步处理地理位置刷新
        $nodes->each(function ($n) use ($node) {
            dispatch(static function () use ($n, $node) {
                $ret = ['node_id' => $n->id, 'status' => 1];
                try {
                    $ret += $n->refresh_geo();
                } catch (Exception $e) {
                    Log::error("节点 [{$n->id}] 刷新地理位置失败: ".$e->getMessage());
                    $ret += ['message' => $e->getMessage()];
                    $ret['status'] = 0;
                }

                broadcast(new NodeActions('geo', $ret, $node?->id));
            });
        });

        return response()->json([
            'status' => 'success',
            'message' => trans('common.success_item', [
                'attribute' => $node ? trans('admin.node.refresh_geo') : trans('admin.node.refresh_geo_all'),
            ]),
        ]);
    }

    public function reload(?Node $node = null): JsonResponse
    {
        $nodes = $node ? collect([$node]) : Node::whereStatus(1)->whereType(4)->get();

        // 发送节点列表信息
        broadcast(new NodeActions('reload', ['list' => $nodes->pluck('name', 'id')], $node?->id));

        // 异步处理节点重载
        $nodes->each(function ($n) use ($node) {
            dispatch(static function () use ($n, $node) {
                $ret = ['node_id' => $n->id, 'status' => 1];
                try {
                    $ret = array_merge($ret, (new ReloadNode($n))->handle());
                    if (count($ret['error'] ?? [])) {
                        $ret['status'] = 0;
                    }
                } catch (Exception $e) {
                    Log::error("节点 [{$n->id}] 重载失败: ".$e->getMessage());
                    $ret['message'] = $e->getMessage();
                    $ret['status'] = 0;
                }

                broadcast(new NodeActions('reload', $ret, $node?->id));
            });
        });

        return response()->json([
            'status' => 'success',
            'message' => trans('common.success_item', [
                'attribute' => $node ? trans('admin.node.reload') : trans('admin.node.reload_all'),
            ]),
        ]);
    }

    public function nodeMonitor(Node $node): View
    { // 节点流量监控
        return view('admin.node.monitor', ['nodeName' => $node->name, 'nodeServer' => $node->server, ...$this->DataFlowChart($node->id, true)]);
    }
}
