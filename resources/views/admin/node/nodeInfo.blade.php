@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/switchery/switchery.min.css" type="text/css" rel="stylesheet">
	<style type="text/css">
		.hidden {
			display: none
		}
	</style>
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">@isset($node) 编辑节点 @else 添加节点 @endisset</h2>
			</div>
			<div class="alert alert-info" role="alert">
				<button class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">{{trans('home.close')}}</span>
				</button>
				<strong>注意：</strong> 添加节点后自动生成的<code>ID</code>，即为该节点部署ShadowsocksR Python版后端时<code>usermysql.json</code>中的<code>node_id</code>的值，同时也是部署V2Ray后端时的<code>nodeId</code>的值；
			</div>
			<div class="panel-body">
				<form action=@isset($node){{url('/node/edit')}} @else {{url('/node/add')}} @endisset method="post" enctype="multipart/form-data" class="form-horizontal" onsubmit="return Submit()">
					<div class="row">
						<div class="col-lg-6">
							<div class="example-wrap">
								<h4 class="example-title">基础信息</h4>
								<div class="example">
									<div class="form-group row">
										<label for="is_ddns" class="col-md-3 col-form-label">DDNS</label>
										<div class="col-md-9">
											<input type="checkbox" id="is_ddns" name="is_ddns" data-plugin="switchery" onchange="switchSetting('is_ddns')">
										</div>
										<div class="text-help offset-md-3">
											动态IP节点需要<a href="https://github.com/NewFuture/DDNS" target="_blank">配置DDNS</a>，对此类型节点，节点阻断功能会通过域名进行检测
										</div>
									</div>
									<div class="form-group row">
										<label for="name" class="col-md-3 col-form-label"> 节点名称 </label>
										<input type="text" class="form-control col-md-4" name="name" id="name" required>
									</div>
									<div class="form-group row">
										<label for="server" class="col-md-3 col-form-label"> 域名 </label>
										<input type="text" class="form-control col-md-4" name="server" id="server" placeholder="服务器域名地址，填则优先取域名地址">
										<span class="text-help offset-md-3">如果开启Namesilo且域名是Namesilo上购买的，则会强制更新域名的DNS记录为本节点IP，如果其他节点绑定了该域名则会清空其域名信息</span>
									</div>
									<div class="form-group row">
										<label for="ip" class="col-md-3 col-form-label"> IPv4地址 </label>
										<input type="text" class="form-control col-md-4" name="ip" id="ip"
												placeholder="服务器IPv4地址" required>
									</div>
									<div class="form-group row">
										<label for="ipv6" class="col-md-3 col-form-label"> IPv6地址 </label>
										<input type="text" class="form-control col-md-4" name="ipv6" id="ipv6"
												placeholder="服务器IPv6地址，填写则用户可见，域名无效">
									</div>
									<div class="form-group row">
										<label for="push_port" class="col-md-3 col-form-label"> 消息推送端口 </label>
										<input type="number" class="form-control col-md-4" name="push_port" value="0" id="push_port">
										<span class="text-help offset-md-3">必填且防火墙需放行，否则将导致消息推送异常</span>
									</div>
									<div class="form-group row">
										<label for="traffic_rate" class="col-md-3 col-form-label"> 流量比例 </label>
										<input type="number" class="form-control col-md-4" name="traffic_rate"
												value="1.0" id="traffic_rate" step="0.01" required>
										<div class="text-help offset-md-3"> 举例：0.1用100M结算10M，5用100M结算500M</div>
									</div>
									<div class="form-group row">
										<label for="level" class="col-md-3 col-form-label">等级</label>
										<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="level" name="level">
											@foreach($level_list as $level)
												<option value="{{$level->level}}">{{$level->name}}</option>
											@endforeach
										</select>
										<div class="text-help offset-md-3"> 等级：0-无等级，全部可见</div>
									</div>
									<div class="form-group row">
										<label for="speed_limit" class="col-md-3 col-form-label">节点限速</label>
										<div class="col-md-4 input-group p-0">
											<input type="number" class="form-control" id="speed_limit" name="speed_limit" value="1000" required>
											<span class="input-group-text">Mbps</span>
										</div>
									</div>
									<div class="form-group row">
										<label for="client_limit" class="col-md-3 col-form-label">设备数限制</label>
										<input type="number" class="form-control col-md-4" id="client_limit" name="client_limit" value="1000" required>
									</div>
									<div class="form-group row">
										<label for="labels" class="col-md-3 col-form-label">标签</label>
										<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="labels" name="labels" multiple>
											@foreach($label_list as $label)
												<option value="{{$label->id}}">{{$label->name}}</option>
											@endforeach
										</select>
									</div>
									<div class="form-group row">
										<label for="country_code" class="col-md-3 col-form-label"> 国家/地区 </label>
										<select data-plugin="selectpicker" data-style="btn-outline btn-primary"
												class="col-md-5 form-control" name="country_code" id="country_code">
											<option value="un" selected hidden>请选择</option>
											@foreach($country_list as $country)
												<option value="{{$country->code}}">{{$country->code}}- {{$country->name}}</option>
											@endforeach
										</select>
									</div>
									<div class="form-group row">
										<label for="description" class="col-md-3 col-form-label"> 描述 </label>
										<input type="text" class="form-control col-md-6" name="description" id="description" placeholder="简单描述">
									</div>
									<div class="form-group row">
										<label for="sort" class="col-md-3 col-form-label">排序</label>
										<input type="text" class="form-control col-md-4" name="sort" id="sort" value="1" required/>
										<div class="text-help offset-md-3"> 排序值越大排越前 &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</div>
									</div>
									<div class="form-group row">
										<label for="is_udp" class="col-md-3 col-form-label">UDP</label>
										<div class="col-md-9">
											<input type="checkbox" id="is_udp" name="is_udp" data-plugin="switchery">
										</div>
									</div>
									<div class="form-group row">
										<label for="status" class="col-md-3 col-form-label">状态</label>
										<div class="col-md-9">
											<input type="checkbox" id="status" name="status" data-plugin="switchery">
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="example-wrap">
								<h4 class="example-title">扩展信息</h4>
								<div class="example">
									<div class="form-group row">
										<label for="type" class="col-md-3 col-form-label">类型</label>
										<ul class="col-md-9 list-unstyled list-inline">
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" id="shadowsocks" name="type" value="1" checked>
													<label for="shadowsocks">Shadowsocks(R)</label>
												</div>
											</li>
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" id="v2ray" name="type" value="2">
													<label for="v2ray">V2Ray</label>
												</div>
											</li>
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" id="trojan" name="type" value="3">
													<label for="trojan">Trojan</label>
												</div>
											</li>
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" id="vnet" name="type" value="4" checked>
													<label for="vnet">VNET</label>
												</div>
											</li>
										</ul>
									</div>
									<hr/>
									<!-- SS/SSR 设置部分 -->
									<div class="ssr-setting">
										<div class="form-group row">
											<label for="method" class="col-md-3 col-form-label">加密方式</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="method" id="method">
												@foreach ($method_list as $method)
													<option value="{{$method->name}}" @if(!isset($node) && $method->is_default) selected @endif>{{$method->name}}</option>
												@endforeach
											</select>
										</div>
										<div class="form-group row">
											<label for="protocol" class="col-md-3 col-form-label">协议</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="protocol" id="protocol">
												@foreach ($protocol_list as $protocol)
													<option value="{{$protocol->name}}" @if(!isset($node) && $protocol->is_default) selected @endif>{{$protocol->name}}</option>
												@endforeach
											</select>
										</div>
										<div class="form-group row">
											<label for="protocol_param" class="col-md-3 col-form-label"> 协议参数 </label>
											<input type="text" class="form-control col-md-4" name="protocol_param" id="protocol_param">
										</div>
										<div class="form-group row">
											<label for="obfs" class="col-md-3 col-form-label">混淆</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="obfs" id="obfs">
												@foreach ($obfs_list as $obfs)
													<option value="{{$obfs->name}}" @if(!isset($node) && $obfs->is_default) selected @endif>{{$obfs->name}}</option>
												@endforeach
											</select>
										</div>
										<div class="form-group row obfs_param">
											<label for="obfs_param" class="col-md-3 col-form-label"> 混淆参数 </label>
											<textarea class="form-control col-md-8" rows="5" name="obfs_param" id="obfs_param" placeholder="混淆不为 [plain] 时可填入参数进行流量伪装；&#13;&#10;混淆为 [http_simple] 时，建议端口为 80；&#13;&#10;混淆为 [tls] 时，建议端口为 443；"></textarea>
										</div>
										<div class="form-group row">
											<label for="compatible" class="col-md-3 col-form-label">兼容SS</label>
											<div class="col-md-9">
												<input type="checkbox" id="compatible" name="compatible" data-plugin="switchery">
											</div>
											<div class="text-help offset-md-3">
												如果兼容请在服务端配置协议和混淆时加上<span class="red-700">_compatible</span>
											</div>
										</div>
										<hr/>
										<div class="form-group row">
											<label for="single" class="col-md-3 col-form-label">单端口</label>
											<div class="col-md-9">
												<input type="checkbox" id="single" name="single" data-plugin="switchery" onchange="switchSetting('single')">
											</div>
											<div class="text-help offset-md-3">
												如果启用请配置服务端的<span class="red-700"><a href="javascript:showTnc();">additional_ports</a></span>信息
											</div>
										</div>
										<div class="single-setting hidden">
											<div class="form-group row">
												<label for="single_port" class="col-md-3 col-form-label">[单] 端口</label>
												<input type="number" class="form-control col-md-4" name="port" value="443" id="single_port"/>
												<span class="text-help offset-md-3"> 推荐80或443，服务端需要配置 </span>
												<span class="text-help offset-md-3">
													严格模式：用户的端口无法连接，只能通过以下指定的端口进行连接 （<a href="javascript:showPortsOnlyConfig();">如何配置</a>）</span>
											</div>
											<div class="form-group row">
												<label for="passwd" class="col-md-3 col-form-label">[单] 密码</label>
												<input type="text" class="form-control col-md-4" name="passwd" id="passwd" placeholder="password">
											</div>
										</div>
									</div>
									<!-- V2ray 设置部分 -->
									<div class="v2ray-setting hidden">
										<div class="form-group row">
											<label for="v2_alter_id" class="col-md-3 col-form-label">额外ID</label>
											<input type="text" class="form-control col-md-4" name="v2_alter_id" value="16" id="v2_alter_id" required/>
										</div>
										<div class="form-group row">
											<label for="v2ray_port" class="col-md-3 col-form-label">连接端口</label>
											<input type="number" class="form-control col-md-4" name="port" id="v2ray_port" value="443"/>
										</div>
										<div class="form-group row">
											<label for="v2_port" class="col-md-3 col-form-label">服务端口</label>
											<input type="number" class="form-control col-md-4" name="v2_port" id="v2_port" value="10053" required/>
										</div>
										<div class="form-group row">
											<label for="v2_method" class="col-md-3 col-form-label">加密方式</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" id="v2_method">
												<option value="auto">auto</option>
												<option value="none">none</option>
												<option value="aes-128-gcm">aes-128-gcm</option>
												<option value="chacha20-poly1305">chacha20-poly1305</option>
											</select>
											<div class="text-help offset-md-3"> 使用WebSocket传输协议时不要使用none</div>
										</div>
										<div class="form-group row">
											<label for="v2_net" class="col-md-3 col-form-label">传输方式</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" id="v2_net">
												<option value="tcp">TCP</option>
												<option value="kcp">mKCP</option>
												<option value="ws">WebSocket</option>
												<option value="http">HTTP/2</option>
												<option value="domainsocket">DomainSocket</option>
												<option value="quic">QUIC</option>
											</select>
											<div class="text-help offset-md-3"> 使用WebSocket传输协议时请启用TLS</div>
										</div>
										<div class="form-group row v2_type">
											<label for="v2_type" class="col-md-3 col-form-label">伪装类型</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" id="v2_type">
												<option value="none">无伪装</option>
												<option value="http">HTTP数据流</option>
												<optgroup id="type_option" label="">
													<option value="srtp">视频通话数据 (SRTP)</option>
													<option value="utp">BT下载数据 (uTP)</option>
													<option value="wechat-video">微信视频通话</option>
													<option value="dtls">DTLS1.2数据包</option>
													<option value="wireguard">WireGuard数据包</option>
												</optgroup>
											</select>
										</div>
										<div class="form-group row v2_host">
											<label for="v2_host" class="col-md-3 col-form-label">伪装域名</label>
											<div class="col-md-4 pl-0">
												<input type="text" class="form-control" name="v2_other" id="v2_host">
												<div name="v2_ws">
													<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control" id="v2_ws">
														<option value="" hidden></option>
														@foreach($dv_list as $dv)
															<option value="{{$dv->domain}}" @if(isset($node) && $node->v2_net == "ws" && $node->v2_host == $dv->domain) selected @endif>{{$dv->domain}}</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="text-help offset-md-3"> 伪装类型为http时多个伪装域名逗号隔开，使用WebSocket传输协议时只允许单个</div>
										</div>
										<div class="form-group row">
											<label for="v2_path" class="col-md-3 col-form-label">路径 | 密钥</label>
											<input type="text" class="form-control col-md-4" name="v2_path" id="v2_path">
										</div>
										<div class="form-group row">
											<label for="v2_tls" class="col-md-3 col-form-label">连接TLS</label>
											<div class="col-md-9">
												<input type="checkbox" id="v2_tls" name="v2_tls" data-plugin="switchery" onchange="switchSetting('v2_tls')">
											</div>
										</div>
										<div class="form-group row">
											<label for="tls_provider" class="col-md-3 col-form-label">TLS配置</label>
											<input type="text" class="form-control col-md-9" name="tls_provider" id="tls_provider"/>
											<div class="text-help offset-md-3"> 不同后端配置不同：
												<a href="https://github.com/Scyllaly/docs/wiki/tls" target="_blank">VNET-V2Ray</a> 、
												<a href="https://github.com/ColetteContreras/v2ray-poseidon/wiki/020-%E5%AF%B9%E6%8E%A5-VNetPanel-%E6%95%99%E7%A8%8B#%E9%85%8D%E7%BD%AE-tls-%E8%AF%81%E4%B9%A6" target="_blank">V2Ray-Poseidon</a>
											</div>
										</div>
									</div>
									<!-- Trojan 设置部分 -->
									<div class="trojan-setting hidden">
										<div class="form-group row">
											<label for="trojan_port" class="col-md-3 col-form-label">连接端口</label>
											<input type="number" class="form-control col-md-4" name="port" id="trojan_port" value="443"/>
										</div>
									</div>
									<!-- 中转 设置部分 -->
									<hr/>
									<div class="form-group row">
										<label for="is_subscribe" class="col-md-3 col-form-label">订阅</label>
										<div class="col-md-9">
											<input type="checkbox" id="is_subscribe" name="is_subscribe" data-plugin="switchery">
										</div>
									</div>
									<div class="form-group row">
										<label for="detection_type" class="col-md-3 col-form-label">节点阻断检测</label>
										<ul class="col-md-9 list-unstyled list-inline">
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" id="detect_disable" name="detection_type" value="0" checked/>
													<label for="detect_disable">关闭</label>
												</div>
											</li>
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" id="detect_tcp" name="detection_type" value="1"/>
													<label for="detect_tcp">只检测TCP</label>
												</div>
											</li>
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" id="detect_icmp" name="detection_type" value="2"/>
													<label for="detect_icmp">只检测ICMP</label>
												</div>
											</li>
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" id="detect_all" name="detection_type" value="3"/>
													<label for="detect_all">检测全部</label>
												</div>
											</li>
										</ul>
										<div class="text-help offset-md-3"> 每30~60分钟随机进行节点阻断检测</div>
									</div>
									<div class="form-group row">
										<label for="is_relay" class="col-md-3 col-form-label">中转</label>
										<div class="col-md-9">
											<input type="checkbox" id="is_relay" name="is_relay" data-plugin="switchery" onchange="switchSetting('is_relay')">
										</div>
									</div>
									<div class="relay-setting hidden">
										<div class="form-group row">
											<label for="relay_port" class="col-md-3 col-form-label"> 中转端口 </label>
											<input type="number" class="form-control col-md-4" name="relay_port" id="relay_port" value="443">
										</div>
										<div class="form-group row">
											<label for="relay_server" class="col-md-3 col-form-label"> 中转地址 </label>
											<input type="text" class="form-control col-md-4" name="relay_server" id="relay_server">
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-12 form-actions">
								<button type="submit" class="btn btn-success">提 交</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/bootstrap-select.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/switchery/switchery.min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/switchery.js" type="text/javascript"></script>

	<script type="text/javascript">
		const string = "{{strtolower(Str::random())}}";
		$(document).ready(function () {
			let v2_path = $('#v2_path');
			@isset($node)

			@if($node->is_ddns)
			$('#is_ddns').click();
			@endif
			$('#name').val('{{$node->name}}');
			$('#server').val('{{$node->server}}');
			$('#ip').val('{{$node->ip}}');
			$('#ipv6').val('{{$node->ipv6}}');
			$('#push_port').val('{{$node->push_port}}');
			$('#traffic_rate').val('{{$node->traffic_rate}}');
			$('#level').selectpicker('val', '{{$node->level}}');
			$('#speed_limit').val('{{$node->speed_limit/Mbps}}');
			$('#client_limit').val('{{$node->client_limit}}');
			$('#labels').selectpicker('val', {{$node->labels}});
			$('#country_code').selectpicker('val', '{{$node->country_code}}');
			$('#description').val('{{$node->description}}');
			$('#sort').val('{{$node->sort}}');
			@if($node->is_udp)
			$('#is_udp').click();
			@endif
			@if($node->status)
			$('#status').click();
			@endif
			@if($node->is_subscribe)
			$('#is_subscribe').click();
			@endif
			$("input[name='detection_type'][value='{{$node->detection_type}}']").click();
			@if($node->single)
			$('#single').click();
			@endif
			$('input[name = port]').val('{{$node->port}}');
			$('#passwd').val('{{$node->passwd}}');
			$("input[name='type'][value='{{$node->type}}']").click();

			@if($node->type == 1 || $node->type == 4)
			// ShadowsocksR
			$('#method').selectpicker('val', '{{$node->method}}');
			$('#protocol').selectpicker('val', '{{$node->protocol}}');
			$('#protocol_param').val('{{$node->protocol_param}}');
			$('#obfs').selectpicker('val', '{{$node->obfs}}');
			$('#obfs_param').val('{{$node->obfs_param}}');
			@if($node->compatible)
			$('#compatible').click();
			@endif
			@endif

			@if($node->type == 2)
			//V2Ray
			$('#v2_alter_id').val('{{$node->v2_alter_id}}');
			$('#v2_port').val('{{$node->v2_port}}');
			$('#v2_method').selectpicker('val', '{{$node->v2_method}}');
			$('#v2_net').selectpicker('val', '{{$node->v2_net}}');
			$('#v2_type').selectpicker('val', '{{$node->v2_type}}');
			$('#v2_host').val('{{$node->v2_host}}');
			v2_path.val('{{$node->v2_path}}');
			@if($node->v2_tls)
			$('#v2_tls').click();
			@endif
			$('#tls_provider').val('{!! $node->tls_provider !!}');
			@endif

			@if($node->is_relay)
			// 中转
			$('#is_relay').click();
			$('#relay_port').val('{{$node->relay_port}}');
			$('#relay_server').val('{{$node->relay_server}}');
			@endif
			@else
			$('#status').click();
			$('#is_udp').click();
			$('#is_subscribe').click();
			v2_path.val('/' + string);
			@endisset
			if ($("#obfs").val() === 'plain') {
				$('.obfs_param').hide();
			}
		});

		// ajax同步提交
		function Submit() {
			$.ajax({
				type: "POST",
				url: @isset($node) "/node/edit" @else "/node/add" @endisset,
				async: false,
				data: {
					_token: '{{csrf_token()}}',
					id: '{{Request::get('id')}}',
					is_ddns: document.getElementById("is_ddns").checked ? 1 : 0,
					name: $('#name').val(),
					server: $('#server').val(),
					ip: $('#ip').val(),
					ipv6: $('#ipv6').val(),
					push_port: $('#push_port').val(),
					traffic_rate: $('#traffic_rate').val(),
					level: $('#level').val(),
					speed_limit: $('#speed_limit').val(),
					client_limit: $('#client_limit').val(),
					labels: $('#labels').val(),
					country_code: $('#country_code option:selected').val(),
					description: $('#description').val(),
					sort: $('#sort').val(),
					is_udp: document.getElementById("is_udp").checked ? 1 : 0,
					status: document.getElementById("status").checked ? 1 : 0,
					type: $("input[name='type']:checked").val(),
					method: $('#method').val(),
					protocol: $('#protocol').val(),
					protocol_param: $('#protocol_param').val(),
					obfs: $('#obfs').val(),
					obfs_param: $('#obfs_param').val(),
					compatible: document.getElementById("compatible").checked ? 1 : 0,
					is_subscribe: document.getElementById("is_subscribe").checked ? 1 : 0,
					detection_type: $("input[name='detection_type']:checked").val(),
					single: document.getElementById("single").checked ? 1 : 0,
					port: $("input[name='port']").val(),
					passwd: $('#passwd').val(),
					v2_alter_id: $('#v2_alter_id').val(),
					v2_port: $('#v2_port').val(),
					v2_method: $("#v2_method").val(),
					v2_net: $('#v2_net').val(),
					v2_type: $('#v2_type').val(),
					v2_host: $('#v2_host').val(),
					v2_path: $('#v2_path').val(),
					v2_tls: document.getElementById("v2_tls").checked ? 1 : 0,
					tls_provider: $('#tls_provider').val(),
					is_relay: document.getElementById("is_relay").checked ? 1 : 0,
					relay_port: $('#relay_port').val(),
					relay_server: $('#relay_server').val(),
				},
				dataType: 'json',
				success: function (ret) {
					if (ret.status === 'success') {
						swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
							.then(() => window.location.href = '/node')
					} else {
						swal.fire({
							title: '[错误 | Error]',
							text: ret.message,
							type: 'error'
						});
					}
				}
			});
			return false;
		}

		function switchSetting(id) {
			let check = document.getElementById(id).checked ? 1 : 0;
			switch (id) {
				// 设置单端口多用户
				case 'single':
					if (check) {
						$(".single-setting").show();
					} else {
						$('#single_port').val('');
						$('#passwd').val('');
						$(".single-setting").hide();
					}
					break;
				//设置中转
				case 'is_relay':
					if (check) {
						$(".relay-setting").show();
						$("#relay_port").attr("required", "required");
						$("#relay_server").attr("required", "required");
					} else {
						$(".relay-setting").hide();
						$("#relay_port").removeAttr("required");
						$("#relay_server").removeAttr("required");
					}
					break;
				// 设置是否使用DDNS
				case 'is_ddns':
					if (check) {
						$("#ip").val("1.1.1.1").attr("readonly", "readonly");
						$("#server").attr("required", "required");
					} else {
						$("#ip").val("").removeAttr("readonly");
						$("#server").removeAttr("required");
					}
					break;
				default:
					break;
			}
		}

		// 设置服务类型
		$("input:radio[name='type']").on('change', function () {
			const type = parseInt($(this).val());
			const $ssr_setting = $(".ssr-setting");
			const $v2ray_setting = $(".v2ray-setting");
			const $trojan_setting = $(".trojan-setting");
			$ssr_setting.hide();
			$v2ray_setting.hide();
			$trojan_setting.hide();
			switch (type) {
				case 1:
					$ssr_setting.show();
					break;
				case 2:
					$v2ray_setting.show();
					$('#v2_net').selectpicker('val', 'tcp');
					break;
				case 3:
					$trojan_setting.show();
					break;
				case 4:
					$ssr_setting.show();
					break;
				default:
			}
		});

		$("#obfs").on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
			const obfs_param = $('.obfs_param');
			if ($("#obfs").val() === 'plain') {
				$("#obfs_param").val('');
				obfs_param.hide();
			} else {
				obfs_param.show();
			}
		});

		$("#v2_ws").on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
			$('#v2_host').val($('#v2_ws').val());
		})

		// 设置V2Ray详细设置
		$("#v2_net").on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
			const type = $('.v2_type');
			const type_option = $('#type_option');
			const host = $('.v2_host');
			const path = $('#v2_path');
			const v2_ws = $('[name="v2_ws"]');
			const v2_other = $('[name="v2_other"]');
			type.show();
			host.show();
			v2_other.show();
			v2_ws.hide();
			path.val('/' + string);
			switch ($(this).val()) {
				case 'kcp':
					type_option.attr('disabled', false);
					break;
				case 'ws':
					v2_ws.show();
					type.hide();
					v2_other.hide();
					break;
				case 'http':
					type.hide();
					break;
				case 'domainsocket':
					type.hide();
					host.hide();
					break;
				case 'quic':
					type_option.attr('disabled', false);
					path.val(string);
					break;
				case 'tcp':
				default:
					type_option.attr('disabled', true);
					break;
			}
			$('#v2_type').selectpicker('refresh');
		});

		// 服务条款
		function showTnc() {
			const content =
				'<ol>' +
				'<li>请勿直接复制黏贴以下配置，SSR(R)会报错的</li>' +
				'<li>确保服务器时间为CST</li>' +
				'</ol>' +
				'&emsp;&emsp;"additional_ports" : {<br />' +
				'&emsp;&emsp;&emsp;"443": {<br />' +
				'&emsp;&emsp;&emsp;&emsp;"passwd": "ProxyPanel",<br />' +
				'&emsp;&emsp;&emsp;&emsp;"method": "none",<br />' +
				'&emsp;&emsp;&emsp;&emsp;"protocol": "auth_chain_a",<br />' +
				'&emsp;&emsp;&emsp;&emsp;"protocol_param": "#",<br />' +
				'&emsp;&emsp;&emsp;&emsp;"obfs": "plain",<br />' +
				'&emsp;&emsp;&emsp;&emsp;"obfs_param": "fe2.update.microsoft.com"<br />' +
				'&emsp;&emsp;&emsp;}<br />' +
				'&emsp;&emsp;},';

			swal.fire({
				title: '[节点 user-config.json 配置示例]',
				html: '<div class="p-10 bg-grey-900 text-white font-weight-300 text-left" style="line-height: 22px;">' + content + '</div>',
				type: 'info'
			});
		}

		// 模式提示
		function showPortsOnlyConfig() {
			const content = '严格模式："additional_ports_only": "true"'
				+ '<br><br>'
				+ '兼容模式："additional_ports_only": "false"';

			swal.fire({
				title: '[节点 user-config.json 配置示例]',
				html: '<div class="p-10 bg-grey-900 text-white font-weight-300 text-left" style="line-height: 22px;">' + content + '</div>',
				type: 'info'
			});
		}
	</script>
@endsection
