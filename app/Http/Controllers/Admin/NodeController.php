<?php

namespace App\Http\Controllers\Admin;

use App\Components\NetworkDetection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NodeRequest;
use App\Jobs\VNet\reloadNode;
use App\Models\Country;
use App\Models\Label;
use App\Models\Level;
use App\Models\Node;
use App\Models\NodeCertificate;
use App\Models\RuleGroup;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class NodeController extends Controller
{
    // 节点列表
    public function index(Request $request)
    {
        $status = $request->input('status');

        $query = Node::with(['onlineLogs', 'dailyDataFlows']);

        if (isset($status)) {
            $query->whereStatus($status);
        }

        $nodeList = $query->orderByDesc('sort')->orderBy('id')->paginate(15)->appends($request->except('page'));
        foreach ($nodeList as $node) {
            $online_log = $node->onlineLogs()->where('log_time', '>=', strtotime('-5 minutes'))->latest('log_time')->first(); // 在线人数
            $node->online_users = $online_log->online_user ?? 0;
            $node->transfer = flowAutoShow($node->dailyDataFlows()->sum('total')); // 已产生流量
            $node_info = $node->heartbeats()->recently()->first(); // 近期负载
            $node->isOnline = ! empty($node_info) && ! empty($node_info->load);
            $node->load = $node_info->load ?? false;
            $node->uptime = empty($node_info) ? 0 : seconds2time($node_info->uptime);
        }

        return view('admin.node.index', ['nodeList' => $nodeList]);
    }

    // 添加节点
    public function store(NodeRequest $request): JsonResponse
    {
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
            'nodes'      => Node::orderBy('id')->pluck('id', 'name'),
            'countries'  => Country::orderBy('code')->get(),
            'levels'     => Level::orderBy('level')->get(),
            'ruleGroups' => RuleGroup::orderBy('id')->get(),
            'labels'     => Label::orderByDesc('sort')->orderBy('id')->get(),
            'certs'      => NodeCertificate::orderBy('id')->get(),
        ]);
    }

    // 添加节点信息
    private function nodeStore(array $info): array
    {
        switch ($info['type']) {
            case 0:
                $profile = [
                    'method' => $info['method'],
                ];
                break;
            case 2:
                $profile = [
                    'method'      => $info['v2_method'],
                    'v2_alter_id' => $info['v2_alter_id'],
                    'v2_net'      => $info['v2_net'],
                    'v2_type'     => $info['v2_type'],
                    'v2_host'     => $info['v2_host'],
                    'v2_path'     => $info['v2_path'],
                    'v2_tls'      => $info['v2_tls'] ? 'tls' : '',
                    'v2_sni'      => $info['v2_sni'],
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
                    'method'         => $info['method'],
                    'protocol'       => $info['protocol'],
                    'obfs'           => $info['obfs'],
                    'obfs_param'     => $info['obfs_param'],
                    'protocol_param' => $info['protocol_param'],
                    'passwd'         => $info['passwd'],
                ];
                break;
        }

        return [
            'type'           => $info['type'],
            'name'           => $info['name'],
            'country_code'   => $info['country_code'],
            'server'         => $info['server'],
            'ip'             => $info['ip'],
            'ipv6'           => $info['ipv6'],
            'level'          => $info['level'],
            'rule_group_id'  => $info['rule_group_id'],
            'speed_limit'    => $info['speed_limit'],
            'client_limit'   => $info['client_limit'],
            'description'    => $info['description'],
            'profile'        => $profile ?? [],
            'traffic_rate'   => $info['traffic_rate'],
            'is_udp'         => $info['is_udp'],
            'is_subscribe'   => $info['is_subscribe'],
            'is_ddns'        => $info['is_ddns'],
            'relay_node_id'  => $info['relay_node_id'],
            'push_port'      => $info['push_port'],
            'detection_type' => $info['detection_type'],
            'sort'           => $info['sort'],
            'status'         => $info['status'],
        ];
    }

    // 编辑节点页面
    public function edit(Node $node)
    {
        return view('admin.node.info', [
            'node'       => $node,
            'nodes'      => Node::whereNotIn('id', [$node->id])->orderBy('id')->pluck('id', 'name'),
            'countries'  => Country::orderBy('code')->get(),
            'levels'     => Level::orderBy('level')->get(),
            'ruleGroups' => RuleGroup::orderBy('id')->get(),
            'labels'     => Label::orderByDesc('sort')->orderBy('id')->get(),
            'certs'      => NodeCertificate::orderBy('id')->get(),
        ]);
    }

    // 编辑节点
    public function update(NodeRequest $request, Node $node): JsonResponse
    {
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

    // 删除节点
    public function destroy(Node $node): JsonResponse
    {
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

    // 节点IP阻断检测
    public function checkNode(Node $node): JsonResponse
    {
        foreach ($node->ips() as $ip) {
            $icmp = (new NetworkDetection)->networkCheck($ip, true, $node->port ?? 22); // ICMP
            $tcp = (new NetworkDetection)->networkCheck($ip, false, $node->port ?? 22); // TCP
            if ($icmp) {
                $data[$ip][0] = config('common.network_status')[$icmp];
            } else {
                $data[$ip][0] = ' ';
            }
            if ($tcp) {
                $data[$ip][1] = config('common.network_status')[$tcp];
            } else {
                $data[$ip][1] = ' ';
            }
        }

        return Response::json(['status' => 'success', 'title' => '['.$node->name.']阻断信息', 'message' => $data ?? []]);
    }

    // 刷新节点地理位置
    public function refreshGeo($id): JsonResponse
    {
        if ($id) {
            $ret = Node::findOrFail($id)->refresh_geo();
        } else {
            foreach (Node::whereStatus(1)->get() as $node) {
                $ret = $node->refresh_geo();
            }
        }

        if ($ret) {
            return Response::json(['status' => 'success', 'message' => '获取地理位置更新成功！']);
        }

        return Response::json(['status' => 'fail', 'message' => '获取地理位置更新失败！']);
    }

    // 重载节点
    public function reload(Node $node): JsonResponse
    {
        if (reloadNode::dispatchNow($node)) {
            return Response::json(['status' => 'success', 'message' => '重载成功！']);
        }

        return Response::json(['status' => 'fail', 'message' => '重载失败！']);
    }

    // 节点流量监控
    public function nodeMonitor(Node $node)
    {
        return view('admin.node.monitor', array_merge(['nodeName' => $node->name, 'nodeServer' => $node->server], $this->DataFlowChart($node->id, true)));
    }

    // Ping节点延迟
    public function pingNode(Node $node): JsonResponse
    {
        if ($result = (new NetworkDetection)->ping($node->is_ddns ? $node->server : $node->ip)) {
            return Response::json([
                'status'  => 'success',
                'message' => $result,
            ]);
        }

        return Response::json(['status' => 'fail', 'message' => 'Ping访问失败']);
    }
}
