<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\NetworkDetection;
use App\Jobs\VNet\reloadNode;
use App\Models\Country;
use App\Models\Label;
use App\Models\Level;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Models\NodeCertificate;
use App\Models\NodePing;
use App\Services\NodeService;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Redirect;
use Response;
use Session;
use Validator;

class NodeController extends Controller {
	// 节点列表
	public function nodeList(Request $request): \Illuminate\Http\Response {
		$status = $request->input('status');

		$query = Node::with(['onlineLogs', 'dailyDataFlows']);

		if(isset($status)){
			$query->whereStatus($status);
		}

		$nodeList = $query->orderByDesc('sort')->orderBy('id')->paginate(15)->appends($request->except('page'));
		foreach($nodeList as $node){
			// 在线人数
			$online_log = $node->onlineLogs()
			                   ->where('log_time', '>=', strtotime("-5 minutes"))
			                   ->latest('log_time')
			                   ->first();
			$node->online_users = empty($online_log)? 0 : $online_log->online_user;

			// 已产生流量
			$node->transfer = flowAutoShow($node->dailyDataFlows()->sum('total'));

			// 负载（10分钟以内）
			$node_info = $node->heartBeats()->recently()->first();
			$node->isOnline = empty($node_info) || empty($node_info->load)? 0 : 1;
			$node->load = $node->isOnline? $node_info->load : '离线';
			$node->uptime = empty($node_info)? 0 : seconds2time($node_info->uptime);
		}

		$view['nodeList'] = $nodeList;

		return Response::view('admin.node.nodeList', $view);
	}

	public function checkNode($id): JsonResponse {
		$node = Node::find($id);
		// 使用DDNS的node先获取ipv4地址
		if($node->is_ddns){
			$ip = gethostbyname($node->server);
			if(strcmp($ip, $node->server) != 0){
				$node->ip = $ip;
			}else{
				return Response::json(['status' => 'fail', 'title' => 'IP获取错误', 'message' => $node->name.'IP获取失败']);
			}
		}
		$data[0] = NetworkDetection::networkCheck($node->ip, true); //ICMP
		$data[1] = NetworkDetection::networkCheck($node->ip, false, $node->single? $node->port : null); //TCP

		return Response::json(['status' => 'success', 'title' => '['.$node->name.']阻断信息', 'message' => $data]);
	}

	// 添加节点
	public function addNode(Request $request) {
		if($request->isMethod('POST')){
			$validator = $this->nodeValidation($request);
			if($validator){
				return $validator;
			}

			// TODO：判断是否已存在绑定了相同域名的节点，提示是否要强制替换，或者不提示之前强制将其他节点的绑定域名置为空，然后发起域名绑定请求，或者请求进入队列
			try{
				DB::beginTransaction();

				$node = new Node();
				$node->type = $request->input('type');
				$node->name = $request->input('name');
				$node->country_code = $request->input('country_code');
				$node->server = $request->input('server');
				$node->ip = $request->input('ip');
				$node->ipv6 = $request->input('ipv6');
				$node->relay_server = $request->input('relay_server');
				$node->relay_port = $request->input('relay_port');
				$node->level = $request->input('level');
				$node->speed_limit = (int) $request->input('speed_limit') * Mbps;
				$node->client_limit = $request->input('client_limit');
				$node->description = $request->input('description');
				$node->method = $request->input('method');
				$node->protocol = $request->input('protocol');
				$node->protocol_param = $request->input('protocol_param');
				$node->obfs = $request->input('obfs');
				$node->obfs_param = $request->input('obfs_param');
				$node->traffic_rate = $request->input('traffic_rate');
				$node->is_subscribe = (int) $request->input('is_subscribe');
				$node->is_ddns = (int) $request->input('is_ddns');
				$node->is_relay = (int) $request->input('is_relay');
				$node->is_udp = (int) $request->input('is_udp');
				$node->push_port = $request->input('push_port');
				$node->detection_type = $request->input('detection_type');
				$node->compatible = (int) $request->input('compatible');
				$node->single = (int) $request->input('single');
				$node->port = $request->input('port');
				$node->passwd = $request->input('passwd');
				$node->sort = $request->input('sort');
				$node->status = (int) $request->input('status');
				$node->v2_alter_id = $request->input('v2_alter_id');
				$node->v2_port = $request->input('v2_port');
				$node->v2_method = $request->input('v2_method');
				$node->v2_net = $request->input('v2_net');
				$node->v2_type = $request->input('v2_type');
				$node->v2_host = $request->input('v2_host')?: '';
				$node->v2_path = $request->input('v2_path');
				$node->v2_tls = (int) $request->input('v2_tls');
				$node->tls_provider = $request->input('tls_provider');
				$node->save();

				DB::commit();
				// 生成节点标签
				(new NodeService())->makeLabels($node->id, $request->input('labels'));

				return Response::json(['status' => 'success', 'message' => '添加成功']);
			}catch(Exception $e){
				DB::rollBack();
				Log::error('添加节点信息异常：'.$e->getMessage());

				return Response::json(['status' => 'fail', 'message' => '添加失败：'.$e->getMessage()]);
			}
		}else{
			$view['methodList'] = Helpers::methodList();
			$view['protocolList'] = Helpers::protocolList();
			$view['obfsList'] = Helpers::obfsList();
			$view['countryList'] = Country::orderBy('code')->get();
			$view['levelList'] = Level::orderBy('level')->get();
			$view['labelList'] = Label::orderByDesc('sort')->orderBy('id')->get();
			$view['dvList'] = NodeCertificate::orderBy('id')->get();

			return Response::view('admin.node.nodeInfo', $view);
		}
	}

	// 节点信息验证
	private function nodeValidation(Request $request) {
		if($request->input('server')){
			$domain = $request->input('server');
			$domain = explode('.', $domain);
			$domainSuffix = end($domain); // 取得域名后缀

			if(!in_array($domainSuffix, config('domains'), true)){
				return Response::json(['status' => 'fail', 'message' => '绑定域名不合法']);
			}
		}

		$validator = Validator::make($request->all(), [
			'type'           => 'required|between:1,3',
			'name'           => 'required',
			'country_code'   => 'required',
			'server'         => 'required_if:is_ddns,1',
			'push_port'      => 'numeric|between:0,65535',
			'traffic_rate'   => 'required|numeric|min:0',
			'level'          => 'required|numeric|between:0,255',
			'speed_limit'    => 'required|numeric|min:0',
			'client_limit'   => 'required|numeric|min:0',
			'port'           => 'nullable|numeric|between:0,65535',
			'ip'             => 'ipv4',
			'ipv6'           => 'nullable|ipv6',
			'relay_server'   => 'required_if:is_relay,1',
			'relay_port'     => 'required_if:is_relay,1|numeric|between:0,65535',
			'method'         => 'required_if:type,1',
			'protocol'       => 'required_if:type,1',
			'obfs'           => 'required_if:type,1',
			'is_subscribe'   => 'boolean',
			'is_ddns'        => 'boolean',
			'is_relay'       => 'boolean',
			'is_udp'         => 'boolean',
			'detection_type' => 'between:0,3',
			'compatible'     => 'boolean',
			'single'         => 'boolean',
			'sort'           => 'required|numeric|between:0,255',
			'status'         => 'boolean',
			'v2_alter_id'    => 'required_if:type,2|numeric|between:0,65535',
			'v2_port'        => 'required_if:type,2|numeric|between:0,65535',
			'v2_method'      => 'required_if:type,2',
			'v2_net'         => 'required_if:type,2',
			'v2_type'        => 'required_if:type,2',
			'v2_tls'         => 'boolean'
		], [
			'server.required_unless' => '开启DDNS， 域名不能为空',
		]);

		if($validator->fails()){
			return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
		}
		return false;
	}

	// 刷新节点地理位置
	public function refreshGeo(Request $request): JsonResponse {
		if((new NodeService())->getNodeGeo($request->input('id', 0))){
			return Response::json(['status' => 'success', 'message' => '获取地理位置更新成功！']);
		}
		return Response::json(['status' => 'fail', 'message' => '获取地理位置更新失败！']);
	}

	// 重载节点
	public function reload($id): JsonResponse {
		if(reloadNode::dispatchNow(Node::whereId($id)->get())){
			return Response::json(['status' => 'success', 'message' => '重载成功！']);
		}
		return Response::json(['status' => 'fail', 'message' => '重载失败！']);
	}

	// 编辑节点
	public function editNode(Request $request) {
		$id = $request->input('id');

		if($request->isMethod('POST')){
			$validator = $this->nodeValidation($request);
			if($validator){
				return $validator;
			}

			$node = Node::find($id);

			try{
				DB::beginTransaction();
				// 生成节点标签
				(new NodeService())->makeLabels($node->id, $request->input('labels'));

				$node->update([
					'type'           => $request->input('type'),
					'name'           => $request->input('name'),
					'country_code'   => $request->input('country_code'),
					'server'         => $request->input('server'),
					'ip'             => $request->input('ip'),
					'ipv6'           => $request->input('ipv6'),
					'relay_server'   => $request->input('relay_server'),
					'relay_port'     => $request->input('relay_port'),
					'level'          => $request->input('level'),
					'speed_limit'    => (int) $request->input('speed_limit') * Mbps,
					'client_limit'   => $request->input('client_limit'),
					'description'    => $request->input('description'),
					'method'         => $request->input('method'),
					'protocol'       => $request->input('protocol'),
					'protocol_param' => $request->input('protocol_param'),
					'obfs'           => $request->input('obfs'),
					'obfs_param'     => $request->input('obfs_param'),
					'traffic_rate'   => $request->input('traffic_rate'),
					'is_subscribe'   => (int) $request->input('is_subscribe'),
					'is_ddns'        => (int) $request->input('is_ddns'),
					'is_relay'       => (int) $request->input('is_relay'),
					'is_udp'         => (int) $request->input('is_udp'),
					'push_port'      => $request->input('push_port'),
					'detection_type' => $request->input('detection_type'),
					'compatible'     => (int) $request->input('compatible'),
					'single'         => (int) $request->input('single'),
					'port'           => $request->input('port'),
					'passwd'         => $request->input('passwd'),
					'sort'           => $request->input('sort'),
					'status'         => (int) $request->input('status'),
					'v2_alter_id'    => $request->input('v2_alter_id'),
					'v2_port'        => $request->input('v2_port'),
					'v2_method'      => $request->input('v2_method'),
					'v2_net'         => $request->input('v2_net'),
					'v2_type'        => $request->input('v2_type'),
					'v2_host'        => $request->input('v2_host')?: '',
					'v2_path'        => $request->input('v2_path'),
					'v2_tls'         => (int) $request->input('v2_tls'),
					'tls_provider'   => $request->input('tls_provider')
				]);
				// TODO:更新节点绑定的域名DNS（将节点IP更新到域名DNS 的A记录）

				DB::commit();

				return Response::json(['status' => 'success', 'message' => '编辑成功']);
			}catch(Exception $e){
				DB::rollBack();
				Log::error('编辑节点信息异常：'.$e->getMessage());

				return Response::json(['status' => 'fail', 'message' => '编辑失败：'.$e->getMessage()]);
			}
		}

		$view['node'] = Node::with('labels')->find($id);
		$view['methodList'] = Helpers::methodList();
		$view['protocolList'] = Helpers::protocolList();
		$view['obfsList'] = Helpers::obfsList();
		$view['countryList'] = Country::orderBy('code')->get();
		$view['levelList'] = Level::orderBy('level')->get();
		$view['labelList'] = Label::orderByDesc('sort')->orderBy('id')->get();
		$view['dvList'] = NodeCertificate::orderBy('id')->get();

		return Response::view('admin.node.nodeInfo', $view);
	}

	// 删除节点
	public function delNode(Request $request): ?JsonResponse {
		$id = $request->input('id');

		$node = Node::find($id);
		if(!$node){
			return Response::json(['status' => 'fail', 'message' => '节点不存在，请重试']);
		}

		try{
			DB::beginTransaction();
			$node->delete();
			DB::commit();

			return Response::json(['status' => 'success', 'message' => '删除成功']);
		}catch(Exception $e){
			DB::rollBack();
			Log::error('删除节点信息异常：'.$e->getMessage());

			return Response::json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
		}
	}

	// 节点流量监控
	public function nodeMonitor(Request $request) {
		$node = Node::find($request->input('id'));
		if(!$node){
			Session::flash('errorMsg', '节点不存在，请重试');

			return Redirect::back();
		}

		$view['nodeName'] = $node->name;
		$view['nodeServer'] = $node->server;
		$view = array_merge($view, $this->DataFlowChart($node->id, 1));

		return Response::view('admin.node.nodeMonitor', $view);
	}

	// Ping节点延迟
	public function pingNode($id): ?JsonResponse {
		$node = Node::find($id);
		if(!$node){
			return Response::json(['status' => 'fail', 'message' => '节点不存在，请重试']);
		}

		$result = NetworkDetection::ping($node->is_ddns? $node->server : $node->ip);

		if($result){
			return Response::json([
				'status'  => 'success',
				'message' => [
					$result['telecom']['time']?: '无',//电信
					$result['Unicom']['time']?: '无',// 联通
					$result['move']['time']?: '无',// 移动
					$result['HongKong']['time']?: '无'// 香港
				]
			]);
		}

		return Response::json(['status' => 'fail', 'message' => 'Ping访问失败']);
	}

	// Ping节点延迟日志
	public function pingLog(Request $request): \Illuminate\Http\Response {
		$node_id = $request->input('nodeId');
		$query = NodePing::query();
		if(isset($node_id)){
			$query->whereNodeId($node_id);
		}

		$view['nodeList'] = Node::orderBy('id')->get();
		$view['pingLogs'] = $query->latest()->paginate(15)->appends($request->except('page'));

		return Response::view('admin.logs.nodePingLog', $view);
	}

	// 节点授权列表
	public function authList(Request $request): \Illuminate\Http\Response {
		$view['list'] = NodeAuth::orderBy('node_id')->paginate(15)->appends($request->except('page'));
		return Response::view('admin.node.authList', $view);
	}

	// 添加节点授权
	public function addAuth(): JsonResponse {
		$nodeArray = Node::whereStatus(1)->orderBy('id')->pluck('id')->toArray();
		$authArray = NodeAuth::orderBy('id')->pluck('node_id')->toArray();

		if($nodeArray == $authArray){
			return Response::json(['status' => 'success', 'message' => '没有需要生成授权的节点']);
		}

		foreach(array_diff($nodeArray, $authArray) as $nodeId){
			$obj = new NodeAuth();
			$obj->node_id = $nodeId;
			$obj->key = makeRandStr(16);
			$obj->secret = makeRandStr(8);
			$obj->save();
		}
		return Response::json(['status' => 'success', 'message' => '生成成功']);
	}

	// 删除节点授权
	public function delAuth(Request $request): JsonResponse {
		try{
			NodeAuth::whereId($request->input('id'))->delete();
		}catch(Exception $e){
			return Response::json(['status' => 'fail', 'message' => '错误：'.var_export($e, true)]);
		}
		return Response::json(['status' => 'success', 'message' => '操作成功']);
	}

	// 重置节点授权
	public function refreshAuth(Request $request): ?JsonResponse {
		$ret = NodeAuth::whereId($request->input('id'))->update([
			'key'    => makeRandStr(16),
			'secret' => makeRandStr(8)
		]);
		if($ret){
			return Response::json(['status' => 'success', 'message' => '操作成功']);
		}

		return Response::json(['status' => 'fail', 'message' => '操作失败']);
	}

	// 域名证书列表
	public function certificateList(Request $request): \Illuminate\Http\Response {
		$DvList = NodeCertificate::orderBy('id')->paginate(15)->appends($request->except('page'));
		foreach($DvList as $Dv){
			if($Dv->key && $Dv->pem){
				$DvInfo = openssl_x509_parse($Dv->pem);
				$Dv->issuer = $DvInfo['issuer']['O'];
				$Dv->from = $DvInfo['validFrom_time_t']? date('Y-m-d', $DvInfo['validFrom_time_t']) : null;
				$Dv->to = $DvInfo['validTo']? date('Y-m-d', $DvInfo['validTo_time_t']) : null;
			}
		}
		$view['list'] = $DvList;
		return Response::view('admin.node.certificateList', $view);
	}

	// 添加域名证书
	public function addCertificate(Request $request) {
		if($request->isMethod('POST')){
			$obj = new NodeCertificate();
			$obj->domain = $request->input('domain');
			$obj->key = str_replace(["\r", "\n"], '', $request->input('key'));
			$obj->pem = str_replace(["\r", "\n"], '', $request->input('pem'));
			$obj->save();

			if($obj->id){
				return Response::json(['status' => 'success', 'message' => '生成成功']);
			}

			return Response::json(['status' => 'fail', 'message' => '生成失败']);
		}

		return Response::view('admin.node.certificateInfo');
	}

	// 编辑域名证书
	public function editCertificate(Request $request) {
		$Dv = NodeCertificate::find($request->input('id'));
		if($request->isMethod('POST')){
			if($Dv){
				$ret = NodeCertificate::whereId($Dv->id)->update([
					'domain' => $request->input('domain'),
					'key'    => $request->input('key'),
					'pem'    => $request->input('pem')
				]);
				if($ret){
					return Response::json(['status' => 'success', 'message' => '修改成功']);
				}
			}
			return Response::json(['status' => 'fail', 'message' => '修改失败']);
		}

		$view['Dv'] = $Dv;
		return Response::view('admin.node.certificateInfo', $view);
	}

	// 删除域名证书
	public function delCertificate(Request $request): JsonResponse {
		try{
			NodeCertificate::whereId($request->input('id'))->delete();
		}catch(Exception $e){
			return Response::json(['status' => 'fail', 'message' => '错误：'.var_export($e, true)]);
		}
		return Response::json(['status' => 'success', 'message' => '操作成功']);
	}
}
