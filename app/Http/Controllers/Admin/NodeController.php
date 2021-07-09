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
use Arr;
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
            $node->isOnline = empty($node_info) || empty($node_info->load) ? 0 : 1;
            $node->load = $node->isOnline ? $node_info->load : '离线';
            $node->uptime = empty($node_info) ? 0 : seconds2time($node_info->uptime);
        }

        return view('admin.node.index', ['nodeList' => $nodeList]);
    }

    // 添加节点页面
    public function create()
    {
        return view('admin.node.info', [
            'countries'  => Country::orderBy('code')->get(),
            'levels'     => Level::orderBy('level')->get(),
            'ruleGroups' => RuleGroup::orderBy('id')->get(),
            'labels'     => Label::orderByDesc('sort')->orderBy('id')->get(),
            'certs'      => NodeCertificate::orderBy('id')->get(),
        ]);
    }

    // 添加节点
    public function store(NodeRequest $request): JsonResponse
    {
        $array = $request->validated();
        Arr::forget($array, ['labels']);
        try {
            if ($node = Node::create($array)) {
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

    // 编辑节点页面
    public function edit(Node $node)
    {
        return view('admin.node.info', [
            'node'       => $node,
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
            $array = $request->validated();
            Arr::forget($array, ['labels']);
            if ($node->update($array)) {
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
            $data[$ip][0] = (new NetworkDetection)->networkCheck($ip, true); // ICMP
            $data[$ip][1] = (new NetworkDetection)->networkCheck($ip, false, $node->single ? $node->port : 22); // TCP
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
