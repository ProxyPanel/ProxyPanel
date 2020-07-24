@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">节点授权列表<small>WEBAPI</small></h2>
				<div class="panel-actions">
					<button class="btn btn-primary" onclick="addAuth()">
						<i class="icon wb-plus" aria-hidden="true"></i>生成授权
					</button>
				</div>
			</div>
			<div class="panel-body">
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 节点ID</th>
						<th> 节点类型</th>
						<th> 节点名称</th>
						<th> 节点域名</th>
						<th> IPv4</th>
						<th> 通信密钥<small>节点用</small></th>
						<th> 反向通信密钥</th>
						<th> 操作</th>
					</tr>
					</thead>
					<tbody>
					@foreach ($list as $vo)
						<tr>
							<td> {{$vo->id}} </td>
							<td> {{$vo->node_id}} </td>
							<td> {{$vo->node->type_label}} </td>
							<td> {{$vo->node ? Str::limit($vo->node->name, 20) : '【节点已删除】'}} </td>
							<td> {{$vo->node ? $vo->node->server : '【节点已删除】'}} </td>
							<td> {{$vo->node ? $vo->node->ip : '【节点已删除】'}} </td>
							<td><span class="badge badge-lg badge-info"> {{$vo->key}} </span></td>
							<td><span class="badge badge-lg badge-info"> {{$vo->secret}} </span></td>
							<td>
								<div class="btn-group">
									<button data-target="#install_{{$vo->node->type}}_{{$vo->id}}" data-toggle="modal" class="btn btn-primary">
										<i class="icon wb-code" aria-hidden="true"></i>部署后端
									</button>
									<button onclick="refreshAuth('{{$vo->id}}')" class="btn btn-danger">
										<i class="icon wb-reload" aria-hidden="true"></i> 重置密钥
									</button>
									<button onclick="deleteAuth('{{$vo->id}}')" class="btn btn-primary">
										<i class="icon wb-trash" aria-hidden="true"></i> 删除
									</button>
								</div>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						共 <code>{{$list->total()}}</code> 条授权
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{$list->links()}}
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>

	@foreach($list as $vl)
		<div id="install_{{$vl->node->type}}_{{$vl->id}}" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
			<div class="modal-dialog modal-simple modal-center modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h4 class="modal-title">
							部署 {{$vl->node->type_label}} 后端
						</h4>
					</div>
					<div class="modal-body">
						@if($vl->node->type === 2)
							<div class="alert alert-info  text-break">
								<div class="text-center red-700 mb-5">VNET-V2Ray</div>
								(yum install curl 2> /dev/null || apt install curl 2> /dev/null) \<br>
								&& curl -L -s https://bit.ly/2xoemF2 \<br>
								| WEB_API="{{\App\Components\Helpers::systemConfig()['web_api_url'] ?: \App\Components\Helpers::systemConfig()['website_url']}}" \<br>
								NODE_ID={{$vl->node->id}} \<br>
								NODE_KEY={{$vl->key}} \<br>
								bash
								<br>
								<br>
								<div class="text-center red-700 mb-5">操作命令</div>
								更新：同上
								<br>
								卸载：curl -L -s https://bit.ly/2xoemF2 | bash -s -- --remove
								<br>
								启动：systemctl start vnet-v2ray
								<br>
								停止：systemctl stop vnet-v2ray
								<br>
								状态：systemctl status vnet-v2ray
								<br>
								近期日志：journalctl -x -n 300 --no-pager -u vnet-v2ray
								<br>
								实时日志：journalctl -u vnet-v2ray -f
							</div>
							<div class="alert alert-info text-break">
								<div class="text-center red-700 mb-5">V2Ray-Poseidon</div>
								(yum install curl 2> /dev/null || apt install curl 2> /dev/null) \<br>
								&& curl -L -s https://bit.ly/2VhvcPz \<br>
								| WEB_API="{{\App\Components\Helpers::systemConfig()['web_api_url'] ?: \App\Components\Helpers::systemConfig()['website_url']}}" \<br>
								NODE_ID={{$vl->node->id}} \<br>
								NODE_KEY={{$vl->key}} \<br>
								bash
								<br>
								<br>
								<div class="text-center red-700 mb-5">操作命令</div>
								更新：curl -L -s https://bit.ly/2VhvcPz | bash
								<br>
								卸载：curl -L -s https://bit.ly/2SGFMMY | bash
								<br>
								启动：systemctl start v2ray
								<br>
								停止：systemctl stop v2ray
								<br>
								状态：systemctl status v2ray
								<br>
								近期日志：journalctl -x -n 300 --no-pager -u v2ray
								<br>
								实时日志：journalctl -u v2ray -f
							</div>
						@elseif($vl->node->type === 3)
							@if(!$vl->node->server)
								<h3>请先<a href="/node/edit?id={{$vl->node->id}}" target="_blank">填写节点域名</a>并将域名解析到节点对应的IP上</h3>
							@else
								<div class="alert alert-info text-break">
									<div class="text-center red-700 mb-5">Trojan-Poseidon</div>
									(yum install curl 2> /dev/null || apt install curl 2> /dev/null) \<br>
									&& curl -L -s https://bit.ly/33UdELu \<br>
									| WEB_API="{{\App\Components\Helpers::systemConfig()['web_api_url'] ?: \App\Components\Helpers::systemConfig()['website_url']}}" \<br>
									NODE_ID={{$vl->node->id}} \<br>
									NODE_KEY={{$vl->key}} \<br>
									NODE_HOST={{$vl->node->server}} \<br>
									bash
									<br>
									<br>
									<div class="text-center red-700 mb-5">操作命令</div>
									更新：curl -L -s https://bit.ly/3esZ7ec | bash
									<br>
									卸载：curl -L -s https://bit.ly/2Jl9bs7 | bash
									<br>
									启动：systemctl start trojanp
									<br>
									停止：systemctl stop trojanp
									<br>
									状态：systemctl status trojanp
									<br>
									近期日志：journalctl -x -n 300 --no-pager -u trojanp
									<br>
									实时日志：journalctl -u trojanp -f
								</div>
							@endif
						@else
							<div class="alert alert-info text-break">
								<div class="text-center red-700 mb-5">VNET</div>
								(yum install curl 2> /dev/null || apt install curl 2> /dev/null) \<br>
								&& curl -L -s https://bit.ly/2RNkPk7 \<br>
								| WEB_API="{{\App\Components\Helpers::systemConfig()['web_api_url'] ?: \App\Components\Helpers::systemConfig()['website_url']}}" \<br>
								NODE_ID={{$vl->node->id}} \<br>
								NODE_KEY={{$vl->key}} \<br>
								bash
								<br>
								<br>
								<div class="text-center red-700 mb-5">操作命令</div>
								更新：同上
								<br>
								卸载：curl -L -s https://bit.ly/2RNkPk7 | bash -s -- --remove
								<br>
								启动：systemctl start vnet
								<br>
								停止：systemctl stop vnet
								<br>
								重启：systemctl restart vnet
								<br>
								状态：systemctl status vnet
								<br>
								近期日志：journalctl -x -n 300 --no-pager -u vnet
								<br>
								实时日志：journalctl -u vnet -f
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	@endforeach

@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>

	<script type="text/javascript">
		// 生成授权KEY
		function addAuth() {
			swal.fire({
				title: '提示',
				text: '确定生成所有节点的授权吗?',
				type: 'info',
				showCancelButton: true,
				cancelButtonText: '{{trans('home.ticket_close')}}',
				confirmButtonText: '{{trans('home.ticket_confirm')}}',
			}).then((result) => {
				if (result.value) {
					$.post("/node/auth/add", {_token: '{{csrf_token()}}'}, function (ret) {
						if (ret.status === 'success') {
							swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
								.then(() => window.location.reload())
						} else {
							swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
						}
					});
				}
			});
		}

		// 删除授权
		function deleteAuth(id) {
			swal.fire({
				title: '提示',
				text: '确定删除该授权吗?',
				type: 'info',
				showCancelButton: true,
				cancelButtonText: '{{trans('home.ticket_close')}}',
				confirmButtonText: '{{trans('home.ticket_confirm')}}',
			}).then((result) => {
				if (result.value) {
					$.post("/node/auth/delete", {_token: '{{csrf_token()}}', id: id}, function (ret) {
						if (ret.status === 'success') {
							swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
								.then(() => window.location.reload())
						} else {
							swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
						}
					});
				}
			});
		}

		// 重置授权认证KEY
		function refreshAuth(id) {
			swal.fire({
				title: '提示',
				text: '确定继续操作吗?',
				type: 'info',
				showCancelButton: true,
				cancelButtonText: '{{trans('home.ticket_close')}}',
				confirmButtonText: '{{trans('home.ticket_confirm')}}',
			}).then((result) => {
				if (result.value) {
					$.post("/node/auth/refresh", {_token: '{{csrf_token()}}', id: id}, function (ret) {
						if (ret.status === 'success') {
							swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
								.then(() => window.location.reload())
						} else {
							swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
						}
					});
				}
			});
		}
	</script>
@endsection