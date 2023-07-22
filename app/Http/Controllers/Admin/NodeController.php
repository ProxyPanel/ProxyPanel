<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DataChart;
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
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class NodeController extends Controller
{
    use DataChart;

    public function index(Request $request) // 节点列表
    {
        $status = $request->input('status');

        $query = Node::whereNull('relay_node_id')->with(['onlineLogs', 'dailyDataFlows:node_id,u,d', 'heartbeats', 'childNodes']);

        if (isset($status)) {
            $query->whereStatus($status);
        }

        $nodeList = $query->orderByDesc('sort')->orderBy('id')->paginate(15)->appends($request->except('page'));
        foreach ($nodeList as $node) {
            $node->online_users = $node->onlineLogs->where('log_time', '>=', strtotime('-5 minutes'))->sortBy('log_time')->first()?->online_user; // 在线人数
            $node->transfer = formatBytes($node->dailyDataFlows->sum('u') + $node->dailyDataFlows->sum('d')); // 已产生流量
            $node_info = $node->heartbeats->where('log_time', '>=', strtotime(config('tasks.recently_heartbeat')))->sortBy('log_time')->first(); // 近期负载
            $node->isOnline = $node_info !== null && ! empty($node_info->load);
            $node->load = $node_info->load ?? false;
            $node->uptime = $node_info === null ? 0 : formatTime($node_info->uptime);
        }

        return view('admin.node.index', ['nodeList' => $nodeList]);
    }

    public function store(NodeRequest $request): JsonResponse
    { // 添加节点
        try {
            if ($node = Node::create($this->nodeStore($request->validated()))) {
                // 生成节点标签
                if ($request->has('labels')) {
                    $node->labels()->attach($request->input('labels'));
                }

                return Response::json(['status' => 'success', 'message' => '添加成功']);
            }
        } catch (Exception $e) {
            Log::error('添加节点信息异常：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '添加线路失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '添加线路失败']);
    }

    public function create()
    {
        return view('admin.node.info', [
            'nodes' => Node::orderBy('id')->pluck('id', 'name'),
            'countries' => Country::orderBy('code')->get(),
            'levels' => Level::orderBy('level')->get(),
            'ruleGroups' => RuleGroup::orderBy('id')->get(),
            'labels' => Label::orderByDesc('sort')->orderBy('id')->get(),
            'certs' => NodeCertificate::orderBy('id')->get(),
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
        $new = $node->replicate()->fill([
            'name' => $node->name.'_克隆',
            'server' => null,
        ]);
        $new->save();

        return redirect()->route('admin.node.edit', $new);
    }

    public function edit(Node $node)
    { // 编辑节点页面
        return view('admin.node.info', [
            'node' => $node,
            'nodes' => Node::whereNotIn('id', [$node->id])->orderBy('id')->pluck('id', 'name'),
            'countries' => Country::orderBy('code')->get(),
            'levels' => Level::orderBy('level')->get(),
            'ruleGroups' => RuleGroup::orderBy('id')->get(),
            'labels' => Label::orderByDesc('sort')->orderBy('id')->get(),
            'certs' => NodeCertificate::orderBy('id')->get(),
        ]);
    }

    public function update(NodeRequest $request, Node $node): JsonResponse
    { // 编辑节点
        try {
            if ($node->update($this->nodeStore($request->validated()))) {
                // 更新节点标签
                $node->labels()->sync($request->input('labels'));

                return Response::json(['status' => 'success', 'message' => '编辑成功']);
            }
        } catch (Exception $e) {
            Log::error('编辑节点信息异常：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '编辑失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '编辑失败']);
    }

    public function destroy(Node $node): JsonResponse
    { // 删除节点
        try {
            if ($node->delete()) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('删除线路失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除线路失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除线路失败']);
    }

    public function checkNode(Node $node): JsonResponse
    { // 节点IP阻断检测
        foreach ($node->ips() as $ip) {
            $status = (new NetworkDetection)->networkStatus($ip, $node->port ?? 22);
            $data[$ip] = [config('common.network_status')[$status['icmp']], config('common.network_status')[$status['tcp']]];
        }

        return Response::json(['status' => 'success', 'title' => '['.$node->name.']阻断信息', 'message' => $data ?? []]);
    }

    public function refreshGeo($id): JsonResponse
    { // 刷新节点地理位置
        $ret = false;
        if ($id) {
            $ret = Node::findOrFail($id)->refresh_geo();
        } else {
            foreach (Node::whereStatus(1)->get() as $node) {
                $result = $node->refresh_geo();
                if ($result && ! $ret) {
                    $ret = true;
                }
            }
        }

        if ($ret) {
            return Response::json(['status' => 'success', 'message' => '获取地理位置更新成功！']);
        }

        return Response::json(['status' => 'fail', 'message' => '【存在】获取地理位置更新失败！']);
    }

    public function reload($id): JsonResponse
    { // 重载节点
        $ret = false;
        if ($id) {
            $node = Node::findOrFail($id);
            $ret = reloadNode::dispatchSync($node);
        } else {
            foreach (Node::whereStatus(1)->whereType(4)->get() as $node) {
                $result = reloadNode::dispatchSync($node);
                if ($result && ! $ret) {
                    $ret = true;
                }
            }
        }

        if ($ret) {
            return Response::json(['status' => 'success', 'message' => '重载成功！']);
        }

        return Response::json(['status' => 'fail', 'message' => '【存在】重载失败！']);
    }

    public function nodeMonitor(Node $node)
    { // 节点流量监控
        return view('admin.node.monitor', array_merge(['nodeName' => $node->name, 'nodeServer' => $node->server], $this->DataFlowChart($node->id, true)));
    }

    public function pingNode(Node $node): JsonResponse
    { // Ping节点延迟
        if ($node->is_ddns) {
            if ($result = (new NetworkDetection)->ping($node->server)) {
                return Response::json(['status' => 'success', 'message' => $result]);
            }
        } else {
            $msg = null;
            foreach ($node->ips() as $ip) {
                $ret = (new NetworkDetection)->ping($ip);
                if ($ret !== false) {
                    $msg .= $ret.' <hr>';
                }
            }
            if (isset($msg)) {
                return Response::json(['status' => 'success', 'message' => $msg]);
            }
        }

        return Response::json(['status' => 'fail', 'message' => 'Ping访问失败']);
    }
}
