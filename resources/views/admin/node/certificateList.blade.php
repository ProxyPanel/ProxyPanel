@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">域名证书列表<small>（V2Ray节点的伪装域名）</small></h2>
				<div class="panel-actions">
					<a href="/node/certificate/add" class="btn btn-primary">
						<i class="icon wb-plus" aria-hidden="true"></i>添加域名证书
					</a>
				</div>
			</div>
			<div class="panel-body">
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 域名</th>
						<th> KEY</th>
						<th> PEM</th>
						<th> 签发机构</th>
						<th> 签发日期</th>
						<th> 到期时间</th>
						<th> 操作</th>
					</tr>
					</thead>
					<tbody>
					@foreach ($list as $vo)
						<tr>
							<td> {{$vo->id}} </td>
							<td> {{$vo->domain}} </td>
							<td> {{$vo->key ? '✔️' : '❌'}} </td>
							<td> {{$vo->pem ? '✔️' : '❌'}} </td>
							<td> {{$vo->issuer}} </td>
							<td> {{$vo->from}} </td>
							<td> {{$vo->to}} </td>
							<td>
								<div class="btn-group">
									<a href="/node/certificate/edit?id={{$vo->id}}" class="btn btn-primary">
										<i class="icon wb-edit" aria-hidden="true"></i>
									</a>
									<button onclick="delCertificate('{{$vo->id}}')" class="btn btn-danger">
										<i class="icon wb-trash" aria-hidden="true"></i>
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
						共 <code>{{$list->total()}}</code> 个域名证书
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
@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>

	<script type="text/javascript">
		// 删除授权
		function delCertificate(id) {
			swal.fire({
				title: '提示',
				text: '确定删除该证书吗?',
				type: 'info',
				showCancelButton: true,
				cancelButtonText: '{{trans('home.ticket_close')}}',
				confirmButtonText: '{{trans('home.ticket_confirm')}}',
			}).then((result) => {
				if (result.value) {
					$.post("/node/certificate/delete", {_token: '{{csrf_token()}}', id: id}, function (ret) {
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