@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">订阅设备列表</h3>
			</div>
			<div class="panel-body">
				<div class="form-row">
					<div class="form-group col-lg-3 col-sm-6">
						<select name="status" id="status" class="form-control" onChange="Search()">
							<option value="" hidden>账号状态</option>
							<option value="-1">禁用</option>
							<option value="0">未激活</option>
							<option value="1">正常</option>
						</select>
					</div>
					<div class="form-group col-lg-2 col-sm-6 btn-group">
						<button class="btn btn-primary" onclick="Search()">搜 索</button>
						<a href="/subscribe/deviceList" class="btn btn-danger">重 置</a>
					</div>
				</div>
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 名称</th>
						<th> 类型</th>
						<th> 平台</th>
						<th> 请求头</th>
						<th> 操作</th>
					</tr>
					</thead>
					<tbody>
					@if($deviceList->isEmpty())
						<tr>
							<td colspan="6">暂无数据</td>
						</tr>
					@else
						@foreach($deviceList as $vo)
							<tr>
								<td> {{$vo->id}} </td>
								<td> {{$vo->name}} </td>
								<td> {!! $vo->type_label !!} </td>
								<td> {!! $vo->platform_label !!} </td>
								<td> {{$vo->header}} </td>
								<td>
									@if($vo->status == 0)
										<button class="btn btn-sm btn-outline-success" onclick="setDeviceStatus('{{$vo->id}}', '1')">启用</button>
									@endif
									@if($vo->status == 1)
										<button class="btn btn-sm btn-outline-danger" onclick="setDeviceStatus('{{$vo->id}}', '0')">禁用</button>
									@endif
								</td>
							</tr>
						@endforeach
					@endif
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						共 <code>{{$deviceList->total()}}</code> 条记录
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{$deviceList->links()}}
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
        $(document).ready(function () {
            $('#status').val({{Request::get('status')}});
        });

        // 搜索
        function Search() {
            window.location.href = '/subscribe/deviceList' + '?status=' + $("#status option:selected").val();
        }

        // 启用禁用订阅设备
        function setDeviceStatus(id, status) {
            $.post("/subscribe/setDeviceStatus", {
                _token: '{{csrf_token()}}',
                id: id,
                status: status
            }, function (ret) {
                swal.fire({text: ret.message, timer: 1000, showConfirmButton: false,})
                    .then(() => {
                        window.location.reload();
                    })
            });
        }
	</script>
@endsection
