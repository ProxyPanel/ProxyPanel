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
use App\Models\NodeAuth;
use App\Models\NodeCertificate;
use App\Models\NodePing;
use App\Services\NodeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Redirect;
use Response;
use Session;
use Str;

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
            // 在线人数
            $online_log = $node->onlineLogs()->where('log_time', '>=', strtotime("-5 minutes"))->latest('log_time')->first();
            $node->online_users = $online_log->online_user ?? 0;

            // 已产生流量
            $node->transfer = flowAutoShow($node->dailyDataFlows()->sum('total'));

            // 负载（10分钟以内）
            $node_info = $node->heartBeats()->recently()->first();
            $node->isOnline = empty($node_info) || empty($node_info->load) ? 0 : 1;
            $node->load = $node->isOnline ? $node_info->load : '离线';
            $node->uptime = empty($node_info) ? 0 : seconds2time($node_info->uptime);
        }

        $view['nodeList'] = $nodeList;

        return view('admin.node.index', $view);
    }

    // 添加节点页面
    public function create()
    {
        return view('admin.node.info', [
            'countryList' => Country::orderBy('code')->get(),
            'levelList'   => Level::orderBy('level')->get(),
            'labelList'   => Label::orderByDesc('sort')->orderBy('id')->get(),
            'dvList'      => NodeCertificate::orderBy('id')->get(),
        ]);
    }

    // 添加节点
    public function store(NodeRequest $request): JsonResponse
    {
        try {
            $node = Node::create($request->except('_token', 'labels'));

            if ($node) {
                // 生成节点标签
                if ($request->exists('labels')) {
                    (new NodeService())->makeLabels($node->id, $request->input('labels'));
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
    public function edit($id)
    {
        return view('admin.node.info', [
            'node'        => Node::with('labels')->find($id),
            'countryList' => Country::orderBy('code')->get(),
            'levelList'   => Level::orderBy('level')->get(),
            'labelList'   => Label::orderByDesc('sort')->orderBy('id')->get(),
            'dvList'      => NodeCertificate::orderBy('id')->get(),
        ]);
    }

    // 编辑节点
    public function update(NodeRequest $request, $id): JsonResponse
    {
        $node = Node::find($id);

        try {
            // 生成节点标签
            if ($request->exists('labels')) {
                (new NodeService())->makeLabels($node->id, $request->input('labels'));
            }

            if ($node->update($request->except('_token', 'labels'))) {
                return Response::json(['status' => 'success', 'message' => '编辑成功']);
            }
        } catch (Exception $e) {
            Log::error('编辑节点信息异常：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '编辑失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '编辑失败']);
    }

    // 删除节点
    public function destroy($id): JsonResponse
    {
        $node = Node::findOrFail($id);

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

    // 节点信息验证
    public function checkNode($id): JsonResponse
    {
        $node = Node::find($id);
        // 使用DDNS的node先获取ipv4地址
        if ($node->is_ddns) {
            $ip = gethostbyname($node->server);
            if (strcmp($ip, $node->server) !== 0) {
                $node->ip = $ip;
            } else {
                return Response::json(['status' => 'fail', 'title' => 'IP获取错误', 'message' => $node->name.'IP获取失败',]);
            }
        }
        $data[0] = NetworkDetection::networkCheck($node->ip, true); //ICMP
        $data[1] = NetworkDetection::networkCheck($node->ip, false, $node->single ? $node->port : null); //TCP

        return Response::json(['status' => 'success', 'title' => '['.$node->name.']阻断信息', 'message' => $data,]);
    }

    // 刷新节点地理位置
    public function refreshGeo($id): JsonResponse
    {
        if ((new NodeService())->getNodeGeo($id)) {
            return Response::json(['status' => 'success', 'message' => '获取地理位置更新成功！']);
        }

        return Response::json(['status' => 'fail', 'message' => '获取地理位置更新失败！']);
    }

    // 重载节点
    public function reload($id): JsonResponse
    {
        if (reloadNode::dispatchNow(Node::whereId($id)->get())) {
            return Response::json(['status' => 'success', 'message' => '重载成功！']);
        }

        return Response::json(['status' => 'fail', 'message' => '重载失败！']);
    }

    // 节点流量监控
    public function nodeMonitor($id)
    {
        $node = Node::find($id);
        if (!$node) {
            Session::flash('errorMsg', '节点不存在，请重试');

            return Redirect::back();
        }

        $view['nodeName'] = $node->name;
        $view['nodeServer'] = $node->server;
        $view = array_merge($view, $this->DataFlowChart($node->id, 1));

        return view('admin.node.monitor', $view);
    }

    // Ping节点延迟
    public function pingNode($id): JsonResponse
    {
        $node = Node::findOrFail($id);

        $result = NetworkDetection::ping($node->is_ddns ? $node->server : $node->ip);

        if ($result) {
            return Response::json([
                'status'  => 'success',
                'message' => [
                    $result['telecom']['time'] ?: '无',//电信
                    $result['Unicom']['time'] ?: '无',// 联通
                    $result['move']['time'] ?: '无',// 移动
                    $result['HongKong']['time'] ?: '无'// 香港
                ],
            ]);
        }

        return Response::json(['status' => 'fail', 'message' => 'Ping访问失败']);
    }

    // Ping节点延迟日志
    public function pingLog(Request $request)
    {
        $node_id = $request->input('nodeId');
        $query = NodePing::query();
        if (isset($node_id)) {
            $query->whereNodeId($node_id);
        }

        $view['nodeList'] = Node::orderBy('id')->get();
        $view['pingLogs'] = $query->latest()->paginate(15)->appends($request->except('page'));

        return view('admin.node.ping', $view);
    }

    // 节点授权列表
    public function authList(Request $request)
    {
        $view['list'] = NodeAuth::orderBy('node_id')->paginate(15)->appends($request->except('page'));

        return view('admin.node.auth', $view);
    }

    // 添加节点授权
    public function addAuth(): JsonResponse
    {
        $nodeArray = Node::whereStatus(1)->orderBy('id')->pluck('id')->toArray();
        $authArray = NodeAuth::orderBy('node_id')->pluck('node_id')->toArray();

        $arrayDifferent = array_diff($nodeArray, $authArray);

        if (empty($arrayDifferent)) {
            return Response::json(['status' => 'success', 'message' => '没有需要生成授权的节点']);
        }

        foreach ($arrayDifferent as $nodeId) {
            $obj = new NodeAuth();
            $obj->node_id = $nodeId;
            $obj->key = Str::random();
            $obj->secret = Str::random(8);
            $obj->save();
        }

        return Response::json(['status' => 'success', 'message' => '生成成功']);
    }

    // 删除节点授权
    public function delAuth($id): JsonResponse
    {
        try {
            NodeAuth::whereId($id)->delete();
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '错误：'.var_export($e, true)]);
        }

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }

    // 重置节点授权
    public function refreshAuth($id): JsonResponse
    {
        $ret = NodeAuth::find($id)->update(['key' => Str::random(), 'secret' => Str::random(8)]);
        if ($ret) {
            return Response::json(['status' => 'success', 'message' => '操作成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }
}
