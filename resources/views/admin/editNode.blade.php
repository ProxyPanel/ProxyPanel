@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" type="text/css" rel="stylesheet">
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
				<h2 class="panel-title">节点添加</h2>
			</div>
			<div class="alert alert-info" role="alert">
				<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
				<strong>注意：</strong> 添加节点后自动生成的<code>ID</code>，即为该节点部署ShadowsocksR Python版后端时<code>usermysql.json</code>中的<code>node_id</code>的值，同时也是部署V2Ray后端时的<code>nodeId</code>的值；
			</div>
			<div class="panel-body">
				<form action="/admin/editNode" method="post" class="form-horizontal" onsubmit="return Submit()">
					<div class="row">
						<div class="col-lg-6">
							<div class="example-wrap">
								<h4 class="example-title">基础信息</h4>
								<div class="example">
									<div class="form-group row">
										<label for="is_transit" class="col-md-3 col-form-label">中转</label>
										<ul class="col-md-9 list-unstyled list-inline">
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" name="is_transit" value="1" {{$node->is_transit ? 'checked' : ''}}>
													<label>是</label>
												</div>
											</li>
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" name="is_transit" value="0" {{$node->is_transit ? '' : 'checked'}}>
													<label>否</label>
												</div>
											</li>
										</ul>
									</div>
									<div class="form-group row">
										<label for="is_ddns" class="col-md-3 col-form-label">DDNS</label>
										<ul class="col-md-9 list-unstyled list-inline">
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" name="is_ddns" value="1" {{$node->is_ddns ? 'checked' : ''}}>
													<label>是</label>
												</div>
											</li>
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" name="is_ddns" value="0" {{$node->is_ddns ? '' : 'checked'}}>
													<label>否</label>
												</div>
											</li>
										</ul>
										<span class="text-help offset-md-3"> 动态IP节点需要<a href="https://github.com/NewFuture/DDNS" target="_blank">配置DDNS</a>，对此类型节点，节点阻断功能会通过域名进行检测 </span>
									</div>
									<div class="form-group row">
										<label for="name" class="col-md-3 col-form-label"> 节点名称 </label>
										<input type="text" class="form-control col-md-4" name="name" id="name" value="{{$node->name}}" autofocus required>
										<input type="hidden" name="id" value="{{$node->id}}">
									</div>
									<div class="form-group row">
										<label for="server" class="col-md-3 col-form-label"> 域名 </label>
										<input type="text" class="form-control col-md-4" name="server" id="server" value="{{$node->server}}" placeholder="服务器域名地址，填则优先取域名地址">
										<span class="text-help offset-md-3">如果开启Namesilo且域名是Namesilo上购买的，则会强制更新域名的DNS记录为本节点IP，如果其他节点绑定了该域名则会清空其域名信息</span>
									</div>
									<div class="form-group row">
										<label for="ip" class="col-md-3 col-form-label"> IPv4地址 </label>
										<input type="text" class="form-control col-md-4" name="ip" id="ip" value="{{$node->ip}}" placeholder="服务器IPv4地址" {{$node->is_ddns ? 'readonly=readonly' : ''}} required>
									</div>
									<div class="form-group row">
										<label for="ipv6" class="col-md-3 col-form-label"> IPv6地址 </label>
										<input type="text" class="form-control col-md-4" name="ipv6" id="ipv6" value="{{$node->ipv6}}" placeholder="服务器IPv6地址，填写则用户可见，域名无效">
									</div>
									<div class="form-group row">
										<label for="ssh_port" class="col-md-3 col-form-label"> SSH端口 </label>
										<input type="number" class="form-control col-md-4" name="ssh_port" value="{{$node->ssh_port}}" id="ssh_port" placeholder="服务器SSH端口" required>
										<span class="text-help offset-md-3">请务必正确填写此值，否则TCP阻断检测可能误报</span>
									</div>
									<div class="form-group row">
										<label for="traffic_rate" class="col-md-3 col-form-label"> 流量比例 </label>
										<input type="number" class="form-control col-md-4" name="traffic_rate" value="{{$node->traffic_rate}}" id="traffic_rate" required>
										<span class="text-help offset-md-3"> 举例：0.1用100M结算10M，5用100M结算500M </span>
									</div>
									<div class="form-group row">
										<label for="labels" class="col-md-3 col-form-label">标签</label>
										<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="labels" name="labels" multiple>
											@foreach($label_list as $label)
												<option value="{{$label->id}}" @if(in_array($label->id, $node->labels)) selected @endif>{{$label->name}}</option>
											@endforeach
										</select>
									</div>
									<div class="form-group row">
										<label for="group_id" class="col-md-3 col-form-label"> 所属分组 </label>
										<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="group_id" id="group_id" placeholder="请选择">
											@if(!$group_list->isEmpty())
												@foreach($group_list as $group)
													<option value="{{$group->id}}" {{$node->group_id == $group->id ? 'selected' : ''}}>{{$group->name}}</option>
												@endforeach
											@endif
										</select>
										<span class="text-help offset-md-3">订阅时分组展示</span>
									</div>
									<div class="form-group row">
										<label for="country_code" class="col-md-3 col-form-label"> 国家/地区 </label>
										<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="country_code" id="country_code">
											<option value="">请选择</option>
											@if(!$country_list->isEmpty())
												@foreach($country_list as $country)
													<option value="{{$country->code}}" {{$node->country_code == $country->code ? 'selected' : ''}}>{{$country->code}} - {{$country->name}}</option>
												@endforeach
											@endif
										</select>
									</div>
									<div class="form-group row">
										<label for="desc" class="col-md-3 col-form-label"> 描述 </label>
										<input type="text" class="form-control col-md-6" name="desc" id="desc" value="{{$node->desc}}" placeholder="简单描述">
									</div>
									<div class="form-group row">
										<label for="sort" class="col-md-3 col-form-label">排序</label>
										<input type="number" class="form-control col-md-4" name="sort" value="{{$node->sort}}" id="sort">
										<span class="text-help offset-md-3"> 排序值越大排越前 &ensp;&ensp;&ensp;&ensp;&ensp;&ensp;</span>
									</div>
									<div class="form-group row">
										<label for="status" class="col-md-3 col-form-label">状态</label>
										<ul class="col-md-9 list-unstyled list-inline">
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" name="status" value="1" {{$node->status ? 'checked' : ''}}>
													<label>正常</label>
												</div>
											</li>
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" name="status" value="0" {{$node->status ? '' : 'checked'}}>
													<label>维护</label>
												</div>
											</li>
										</ul>
									</div>
								<!--
									<div class="form-group row">
										<label for="bandwidth" class="col-md-3 col-form-label">出口带宽</label>
										<div class="input-group col-md-4">
											<input type="text" class="form-control" name="bandwidth" value="{{$node->bandwidth}}" id="bandwidth" required>
											<span class="input-group-text">M</span>
										</div>
									</div>
									<div class="form-group row">
										<label for="traffic" class="col-md-3 col-form-label">每月可用流量</label>
										<div class="input-group col-md-4">
											<input type="text" class="form-control right" name="traffic" value="{{$node->traffic}}" id="traffic" required>
											<span class="input-group-text">G</span>
										</div>
									</div>
									<div class="form-group row">
										<label for="monitor_url" class="col-md-3 col-form-label">监控地址</label>
										<input type="text" class="form-control col-md-4" name="monitor_url" value="{{$node->monitor_url}}" id="monitor_url" placeholder="节点实时监控地址">
										<span class="text-help offset-md-3"> 例如：http://us1.ssrpanel.com/api/monitor </span>
									</div>
									-->
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="example-wrap">
								<h4 class="example-title">扩展信息</h4>
								<div class="example">
									<div class="form-group row">
										<label for="service" class="col-md-3 col-form-label">类型</label>
										<ul class="col-md-9 list-unstyled list-inline">
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" name="service" value="1" @if($node->type == 1) checked @endif>
													<label>Shadowsocks(R)</label>
												</div>
											</li>
											<li class="list-inline-item">
												<div class="radio-custom radio-primary">
													<input type="radio" name="service" value="2" @if($node->type == 2) checked @endif>
													<label>V2Ray</label>
												</div>
											</li>
										</ul>
									</div>
									<hr/>
									<!-- SS/SSR 设置部分 -->
									<div class="ssr-setting {{$node->type == 1 ? '' : 'hidden'}}">
										<div class="form-group row">
											<label for="method" class="col-md-3 col-form-label">加密方式</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="method" id="method">
												@foreach ($method_list as $method)
													<option value="{{$method->name}}" @if($method->name == $node->method) selected @endif>{{$method->name}}</option>
												@endforeach
											</select>
										</div>
										<div class="form-group row">
											<label for="protocol" class="col-md-3 col-form-label">协议</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="protocol" id="protocol">
												@foreach ($protocol_list as $protocol)
													<option value="{{$protocol->name}}" @if($protocol->name == $node->protocol) selected @endif>{{$protocol->name}}</option>
												@endforeach
											</select>
										</div>
										<div class="form-group row">
											<label for="protocol_param" class="col-md-3 col-form-label"> 协议参数 </label>
											<input type="text" class="form-control col-md-4" name="protocol_param" id="protocol_param" value="{{$node->protocol_param}}">
										</div>
										<div class="form-group row">
											<label for="obfs" class="col-md-3 col-form-label">混淆</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="obfs" id="obfs">
												@foreach ($obfs_list as $obfs)
													<option value="{{$obfs->name}}" @if($obfs->name == $node->obfs) selected @endif>{{$obfs->name}}</option>
												@endforeach
											</select>
										</div>
										<div class="form-group row">
											<label for="obfs_param" class="col-md-3 col-form-label"> 混淆参数 </label>
											<textarea class="form-control col-md-8" rows="5" name="obfs_param" id="obfs_param">{{$node->obfs_param}}</textarea>
										</div>
										<div class="form-group row">
											<label for="compatible" class="col-md-3 col-form-label">兼容SS</label>
											<ul class="col-md-9 list-unstyled list-inline">
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="compatible" value="1" {{$node->compatible ? 'checked' : ''}}>
														<label>是</label>
													</div>
												</li>
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="compatible" value="0" {{$node->compatible ? '' : 'checked'}}>
														<label>否</label>
													</div>
												</li>
											</ul>
											<p class="text-help offset-md-3"> 如果兼容请在服务端配置协议和混淆时加上<span class="red-700">_compatible</span>
											</p>
										</div>
										<div class="form-group row">
											<label for="is_subscribe" class="col-md-3 col-form-label">订阅</label>
											<ul class="col-md-9 list-unstyled list-inline">
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="is_subscribe" value="1" {{$node->is_subscribe ? 'checked' : ''}}>
														<label>允许</label>
													</div>
												</li>
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="is_subscribe" value="0" {{$node->is_subscribe ? '' : 'checked'}}>
														<label>不允许</label>
													</div>
												</li>
											</ul>
										</div>
										<div class="form-group row">
											<label for="detectionType" class="col-md-3 col-form-label">节点阻断检测</label>
											<ul class="col-md-9 list-unstyled list-inline">
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="detectionType" value="0" @if ($node->detectionType == 0) checked @endif/>
														<label>关闭</label>
													</div>
												</li>
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="detectionType" value="1" @if ($node->detectionType == 1) checked @endif/>
														<label>只检测TCP</label>
													</div>
												</li>
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="detectionType" value="2" @if ($node->detectionType == 2) checked @endif/>
														<label>只检测ICMP</label>
													</div>
												</li>
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="detectionType" value="3" @if ($node->detectionType == 3) checked @endif/>
														<label>检测全部</label>
													</div>
												</li>
											</ul>
											<span class="text-help offset-md-3"> 每30~60分钟随机进行节点阻断检测 </span>
										</div>
										<hr/>
										<div class="form-group row">
											<label for="single" class="col-md-3 col-form-label">单端口</label>
											<ul class="col-md-9 list-unstyled list-inline">
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="single" value="1" {{$node->single? 'checked' : ''}}>
														<label>启用</label>
													</div>
												</li>
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="single" value="0" {{$node->single? '' : 'checked'}}>
														<label>关闭</label>
													</div>
												</li>
											</ul>
											<span class="text-help offset-md-3"> 如果启用请配置服务端的<span class="red-700"> <a href="javascript:showTnc();">additional_ports</a> </span>信息 </span>
										</div>
										<div class="single-setting {{!$node->single ? 'hidden' : ''}}">
											<div class="form-group row">
												<label for="port" class="col-md-3 col-form-label">[单] 端口</label>
												<input type="number" class="form-control col-md-4" name="port" id="port" value="{{$node->port}}" placeholder="443">
												<span class="text-help offset-md-3"> 推荐80或443，服务端需要配置 </span>
												<span class="text-help offset-md-3"> 严格模式：用户的端口无法连接，只能通过以下指定的端口进行连接（<a href="javascript:showPortsOnlyConfig();">如何配置</a>）</span>
											</div>
											<div class="form-group row">
												<label for="passwd" class="col-md-3 col-form-label">[单] 密码</label>
												<input type="text" class="form-control col-md-4" name="passwd" id="passwd" value="{{$node->passwd}}" placeholder="password">
											</div>
										</div>
									</div>
									<!-- V2ray 设置部分 -->
									<div class="v2ray-setting {{$node->type == 2 ? '' : 'hidden'}}">
										<div class="form-group row">
											<label for="v2_alter_id" class="col-md-3 col-form-label">额外ID</label>
											<input type="text" class="form-control col-md-4" name="v2_alter_id" id="v2_alter_id" value="{{$node->v2_alter_id}}" placeholder="16">
										</div>
										<div class="form-group row">
											<label for="v2_port" class="col-md-3 col-form-label">端口</label>
											<input type="number" class="form-control col-md-4" name="v2_port" id="v2_port" value="{{$node->v2_port}}" placeholder="10087">
										</div>
										<div class="form-group row">
											<label for="v2_method" class="col-md-3 col-form-label">加密方式</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="v2_method" id="v2_method">
												<option value="none" @if($node->v2_method == 'none') selected @endif>none</option>
												<option value="aes-128-cfb" @if($node->v2_method == 'aes-128-cfb') selected @endif>aes-128-cfb</option>
												<option value="aes-128-gcm" @if($node->v2_method == 'aes-128-gcm') selected @endif>aes-128-gcm</option>
												<option value="chacha20-poly1305" @if($node->v2_method == 'chacha20-poly1305') selected @endif>chacha20-poly1305</option>
											</select>
											<span class="text-help offset-md-3"> 使用WebSocket传输协议时不要使用none </span>
										</div>
										<div class="form-group row">
											<label for="v2_net" class="col-md-3 col-form-label">传输协议</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="v2_net" id="v2_net">
												<option value="tcp" @if($node->v2_net == 'tcp') selected @endif>TCP</option>
												<option value="kcp" @if($node->v2_net == 'kcp') selected @endif>mKCP（kcp）</option>
												<option value="ws" @if($node->v2_net == 'ws') selected @endif>WebSocket（ws）</option>
												<option value="h2" @if($node->v2_net == 'h2') selected @endif>HTTP/2（h2）</option>
											</select>
											<span class="text-help offset-md-3"> 使用WebSocket传输协议时请启用TLS </span>
										</div>
										<div class="form-group row">
											<label for="v2_type" class="col-md-3 col-form-label">伪装类型</label>
											<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="v2_type" id="v2_type">
												<option value="none" @if($node->v2_type == 'none') selected @endif>无伪装</option>
												<option value="http" @if($node->v2_type == 'http') selected @endif>HTTP数据流</option>
												<option value="srtp" @if($node->v2_type == 'srtp') selected @endif>视频通话数据 (SRTP)</option>
												<option value="utp" @if($node->v2_type == 'utp') selected @endif>BT下载数据 (uTP)</option>
												<option value="wechat-video" @if($node->v2_type == 'wechat-video') selected @endif>微信视频通话</option>
												<option value="dtls" @if($node->v2_type == 'dtls') selected @endif>DTLS1.2数据包</option>
												<option value="wireguard" @if($node->v2_type == 'wireguard') selected @endif>WireGuard数据包</option>
											</select>
										</div>
										<div class="form-group row">
											<label for="v2_host" class="col-md-3 col-form-label">伪装域名</label>
											<input type="text" class="form-control col-md-4" name="v2_host" id="v2_host" value="{{$node->v2_host}}">
											<span class="text-help offset-md-3"> 伪装类型为http时多个伪装域名逗号隔开，使用WebSocket传输协议时只允许单个 </span>
										</div>
										<div class="form-group row">
											<label for="v2_path" class="col-md-3 col-form-label">ws/h2路径</label>
											<input type="text" class="form-control col-md-4" name="v2_path" id="v2_path" value="{{$node->v2_path}}">
										</div>
										<div class="form-group row">
											<label for="v2_tls" class="col-md-3 col-form-label">TLS</label>
											<ul class="col-md-9 list-unstyled list-inline">
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="v2_tls" value="1" {{$node->v2_tls? 'checked' : ''}}>
														<label>是</label>
													</div>
												</li>
												<li class="list-inline-item">
													<div class="radio-custom radio-primary">
														<input type="radio" name="v2_tls" value="0" {{$node->v2_tls? '' : 'checked'}}>
														<label>否</label>
													</div>
												</li>
											</ul>
										</div>
										<div class="form-group row">
											<label for="v2_insider_port" class="col-md-3 col-form-label">内部端口</label>
											<input type="text" class="form-control col-md-4" name="v2_insider_port" value="{{$node->v2_insider_port}}" id="v2_insider_port" placeholder="10550">
											<span class="text-help offset-md-3"> 内部监听，当端口为0时启用，仅支持<a href="https://github.com/rico93/pay-v2ray-sspanel-v3-mod_Uim-plugin/" target="_blank">rico93版</a> </span>
										</div>
										<div class="form-group row">
											<label for="v2_outsider_port" class="col-md-3 col-form-label">内部端口</label>
											<input type="text" class="form-control col-md-4" name="v2_outsider_port" value="{{$node->v2_outsider_port}}" id="v2_outsider_port" placeholder="443">
											<span class="text-help offset-md-3"> 外部覆盖，当端口为0时启用，仅支持<a href="https://github.com/rico93/pay-v2ray-sspanel-v3-mod_Uim-plugin/" target="_blank">rico93版</a> </span>
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

	<script type="text/javascript">
        // ajax同步提交
        function Submit() {
            $.ajax({
                type: "POST",
                url: "/admin/editNode",
                async: false,
                data: {
                    _token: '{{csrf_token()}}',
                    id: '{{Request::get('id')}}',
                    name: $('#name').val(),
                    labels: $('#labels').val(),
                    group_id: $('#group_id option:selected').val(),
                    country_code: $('#country_code option:selected').val(),
                    server: $('#server').val(),
                    ip: $('#ip').val(),
                    ipv6: $('#ipv6').val(),
                    desc: $('#desc').val(),
                    method: $('#method').val(),
                    traffic_rate: $('#traffic_rate').val(),
                    protocol: $('#protocol').val(),
                    protocol_param: $('#protocol_param').val(),
                    obfs: $('#obfs').val(),
                    obfs_param: $('#obfs_param').val(),
                    bandwidth: $('#bandwidth').val(),
                    traffic: $('#traffic').val(),
                    monitor_url: $('#monitor_url').val(),
                    is_subscribe: $("input:radio[name='is_subscribe']:checked").val(),
                    is_ddns: $("input:radio[name='is_ddns']:checked").val(),
                    is_transit: $("input:radio[name='is_transit']:checked").val(),
                    ssh_port: $('#ssh_port').val(),
                    compatible: $("input:radio[name='compatible']:checked").val(),
                    single: $("input:radio[name='single']:checked").val(),
                    port: $('#port').val(),
                    passwd: $('#passwd').val(),
                    sort: $('#sort').val(),
                    status: $("input:radio[name='status']:checked").val(),
                    detectionType: $("input:radio[name='detectionType']:checked").val(),
                    type: $("input:radio[name='service']:checked").val(),
                    v2_alter_id: $('#v2_alter_id').val(),
                    v2_port: $('#v2_port').val(),
                    v2_method: $("#v2_method option:selected").val(),
                    v2_net: $('#v2_net').val(),
                    v2_type: $('#v2_type').val(),
                    v2_host: $('#v2_host').val(),
                    v2_path: $('#v2_path').val(),
                    v2_tls: $("input:radio[name='v2_tls']:checked").val(),
                    v2_insider_port: $('#v2_insider_port').val(),
                    v2_outsider_port: $('#v2_outsider_port').val()
                },
                dataType: 'json',
                success: function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.href = '/admin/nodeList?page={{Request::get('page', 1)}}')
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                }
            });
            return false;
        }

        // 设置单端口多用户
        $("input:radio[name='single']").on('change', function () {
            if (parseInt($(this).val())) {
                $(".single-setting").show();
            } else {
                $(".single-setting").hide();
            }
        });

        // 设置服务类型
        $("input:radio[name='service']").on('change', function () {
            if (parseInt($(this).val()) === 1) {
                $(".ssr-setting").show();
                $(".v2ray-setting").hide();
            } else {
                $(".ssr-setting").hide();
                $(".v2ray-setting").show();
            }
        });

        // 设置是否使用DDNS
        $("input:radio[name='is_ddns']").on('change', function () {
            if (parseInt($(this).val())) {
                $("#ip").val("1.1.1.1").attr("readonly", "readonly");
                $("#server").attr("required", "required");
            } else {
                $("#ip").val("").removeAttr("readonly");
                $("#server").removeAttr("required");
            }
        });

        // 服务条款
        function showTnc() {
            const content =
                '<ol>' +
                '<li>请勿直接复制黏贴以下配置，SSR(R)会报错的</li>' +
                '<li>确保服务器时间为CST</li>' +
                '<li>具体请看<a href="https://github.com/ssrpanel/SSRPanel/wiki/%E5%8D%95%E7%AB%AF%E5%8F%A3%E5%A4%9A%E7%94%A8%E6%88%B7%E7%9A%84%E5%9D%91" target="_blank">WIKI</a></li>' +
                '</ol>' +
                '&emsp;&emsp;"additional_ports" : {<br />' +
                '&emsp;&emsp;&emsp;"443": {<br />' +
                '&emsp;&emsp;&emsp;&emsp;"passwd": "@HentaiCloud!",<br />' +
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
