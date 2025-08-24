<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DataChart;
use App\Helpers\ProxyConfig;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NodeRequest;
use App\Jobs\VNet\reloadNode;
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
        try {
            if ($node = Node::create($this->nodeStore($request->validated()))) {
                if ($request->has('labels')) { // 生成节点标签
                    $node->labels()->attach($request->input('labels'));
                }

                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('model.node.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')]).', '.$e->getMessage()]);
        }

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
        try {
            if ($node->update($this->nodeStore($request->validated()))) {
                // 更新节点标签
                $node->labels()->sync($request->input('labels'));

                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.edit')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.edit'), 'attribute' => trans('model.node.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')])]);
    }

    public function destroy(Node $node): JsonResponse
    { // 删除节点
        try {
            if ($node->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.node.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }

    public function checkNode(Node $node): JsonResponse
    { // 节点IP阻断检测
        foreach ($node->ips() as $ip) {
            $status = (new NetworkDetection)->networkStatus($ip, $node->port ?? 22);
            $data[$ip] = [trans("admin.network_status.{$status['icmp']}"), trans("admin.network_status.{$status['tcp']}")];
        }

        return response()->json(['status' => 'success', 'title' => '['.$node->name.'] '.trans('admin.node.connection_test'), 'message' => $data ?? []]);
    }

    public function refreshGeo(?int $id = null): JsonResponse
    { // 刷新节点地理位置
        $ret = false;
        if ($id) {
            $node = Node::findOrFail($id);
            $ret = $node->refresh_geo();
        } else {
            foreach (Node::whereStatus(1)->get() as $node) {
                $result = $node->refresh_geo();
                if ($result && ! $ret) {
                    $ret = true;
                }
            }
        }

        if ($ret) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.update')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.update')])]);
    }

    public function reload(?int $id = null): JsonResponse
    { // 重载节点
        $ret = false;
        if ($id) {
            $node = Node::findOrFail($id);
            $ret = (new reloadNode($node))->handle();
        } else {
            foreach (Node::whereStatus(1)->whereType(4)->get() as $node) {
                $result = (new reloadNode($node))->handle();
                if ($result && ! $ret) {
                    $ret = true;
                }
            }
        }

        if ($ret) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('admin.node.reload')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('admin.node.reload')])]);
    }

    public function nodeMonitor(Node $node): View
    { // 节点流量监控
        return view('admin.node.monitor', ['nodeName' => $node->name, 'nodeServer' => $node->server, ...$this->DataFlowChart($node->id, true)]);
    }
}
