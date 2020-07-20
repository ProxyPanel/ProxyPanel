@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
	<style>
		#swal2-content {
			display: grid !important;
		}
	</style>
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">节点列表</h3>
				<div class="panel-actions">
					<a href="/node/add" class="btn btn-primary"><i class="icon wb-plus"></i> 添加节点</a>
				</div>
			</div>
			<div class="panel-body">
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> ID</th>
						<th> 类型</th>
						<th> 名称</th>
						<th> IP</th>
						<th> 域名</th>
						<th> 存活</th>
						<th> 状态</th>
						<th> 在线</th>
						<th> 产生流量</th>
						<th> 流量比例</th>
						<th> 扩展</th>
						<th> 操作</th>
					</tr>
					</thead>
					<tbody>
					@foreach($nodeList as $node)
						<tr class="{{!$node->isOnline && $node->status ? 'table-danger' : ''}}">
							<td>
								{{$node->id}}
							</td>
							<td>
								@if($node->is_relay)
									中转
								@else
									{{$node->type_label}}
								@endif
							</td>
							<td> {{$node->name}} </td>
							<td> {{$node->is_ddns ? 'DDNS' : $node->ip}} </td>
							<td> {{$node->server}} </td>
							<td> {{$node->uptime}} </td>
							<td> {{$node->status? $node->load : '维护'}} </td>
							<td> {{$node->online_users}} </td>
							<td> {{$node->transfer}} </td>
							<td> {{$node->traffic_rate}} </td>
							<td>
								@if($node->compatible) <span class="badge badge-lg badge-info">兼</span> @endif
								@if($node->single) <span class="badge badge-lg badge-info">单</span> @endif
								@if(!$node->is_subscribe)
									<span class="badge badge-lg badge-danger"><s>订</s></span> @endif
							</td>
							<td>
								<div class="btn-group">
									<a href="javascript:pingNode('{{$node->id}}')" class="btn btn-primary">
										<i id="ping{{$node->id}}" class="icon wb-order"></i>
									</a>
									<a href="javascript:checkNode('{{$node->id}}')" class="btn btn-primary">
										<i id="node{{$node->id}}" class="icon wb-signal"></i>
									</a>
									<a href="/node/edit?id={{$node->id}}&page={{Request::get('page', 1)}}" class="btn btn-primary">
										<i class="icon wb-edit"></i>
									</a>
									<a href="javascript:delNode('{{$node->id}}','{{$node->name}}')" class="btn btn-danger">
										<i class="icon wb-trash"></i>
									</a>
									<a href="/node/monitor?id={{$node->id}}" class="btn btn-primary">
										<i class="icon wb-stats-bars"></i>
									</a>
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
						共 <code>{{$nodeList->total()}}</code> 条线路
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{$nodeList->links()}}
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"
			type="text/javascript"></script>
	<script type="text/javascript">
		//节点连通性测试
		function checkNode(id) {
			$.ajax({
				type: "POST",
				url: '/node/check',
				data: {_token: '{{csrf_token()}}', id: id},
				beforeSend: function () {
					$("#node" + id).removeClass("wb-signal").addClass("wb-loop icon-spin");
				},
				success: function (ret) {
					if (ret.status === 'success') {
						swal.fire({
							title: ret.title,
							type: 'info',
							html: '<table class="my-20"><thead class="thead-default"><tr><th> ICMP </th> <th> TCP </th></thead><tbody><tr><td>' + ret.message[0] + '</td><td>' + ret.message[1] + '</td></tr></tbody></table>',
							showConfirmButton: false
						})
					} else {
						swal.fire({title: ret.title, type: "error"})
					}
				},
				complete: function () {
					$("#node" + id).removeClass("wb-loop icon-spin").addClass("wb-signal");
				}
			});
		}

		//Ping 节点获取延迟
		function pingNode(id) {
			$.ajax({
				type: "POST",
				url: '/node/ping',
				data: {_token: '{{csrf_token()}}', id: id},
				beforeSend: function () {
					$("#ping" + id).removeClass("wb-order").addClass("wb-loop icon-spin");
				},
				success: function (ret) {
					if (ret.status === 'success') {
						swal.fire({
							type: 'info',
							html: '<table class="my-20"><thead class="thead-default"><tr><th> 电信 </th> <th> 联通 </th> <th> 移动 </th> <th> 香港 </th></thead><tbody><tr><td>' + ret.message[0] + '</td><td>' + ret.message[1] + '</td><td>' + ret.message[2] + '</td><td>' + ret.message[3] + '</td></tr></tbody></table>',
							showConfirmButton: false
						})
					} else {
						swal.fire({title: ret.title, type: "error"})
					}
				},
				complete: function () {
					$("#ping" + id).removeClass("wb-loop icon-spin").addClass("wb-order");
				}
			});
		}

		// 删除节点
		function delNode(id, name) {
			swal.fire({
				title: '警告',
				text: '确定删除节点 【' + name + '】 ?',
				type: 'warning',
				showCancelButton: true,
				cancelButtonText: '{{trans('home.ticket_close')}}',
				confirmButtonText: '{{trans('home.ticket_confirm')}}',
			}).then((result) => {
				if (result.value) {
					$.post("/node/delete", {id: id, _token: '{{csrf_token()}}'}, function (ret) {
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

		// 显示提示
		function showIdTips() {
			swal.fire({
				title: '复制成功',
				type: 'success',
				timer: 1300,
				showConfirmButton: false,
			});
		}
	</script>
@endsection
