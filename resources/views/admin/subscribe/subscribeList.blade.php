@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">订阅列表</h3>
			</div>
			<div class="panel-body">
				<div class="form-row">
					<div class="form-group col-lg-2 col-sm-6">
						<input type="number" class="form-control" name="user_id" id="user_id"
								value="{{Request::get('user_id')}}" placeholder="ID"/>
					</div>
					<div class="form-group col-lg-4 col-sm-6">
						<input type="text" class="form-control" name="email" id="email"
								value="{{Request::get('email')}}" placeholder="用户名"/>
					</div>
					<div class="form-group col-lg-3 col-sm-6">
						<select name="status" id="status" class="form-control" onChange="Search()">
							<option value="" hidden>状态</option>
							<option value="0">禁用</option>
							<option value="1">正常</option>
						</select>
					</div>
					<div class="form-group col-lg-2 col-sm-6 btn-group">
						<button class="btn btn-primary" onclick="Search()">搜 索</button>
						<a href="/subscribe" class="btn btn-danger">重 置</a>
					</div>
				</div>
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 用户</th>
						<th> 订阅码</th>
						<th> 请求次数</th>
						<th> 最后请求时间</th>
						<th> 封禁时间</th>
						<th> 封禁理由</th>
						<th> 操作</th>
					</tr>
					</thead>
					<tbody>
					@foreach($subscribeList as $subscribe)
						<tr>
							<td> {{$subscribe->id}} </td>
							<td>
								@if(empty($subscribe->user))
									【账号已删除】
								@else
									<a href="/admin/userList?id={{$subscribe->user->id}}"
											target="_blank">{{$subscribe->user->email}}</a>
								@endif
							</td>
							<td> {{$subscribe->code}} </td>
							<td>
								<a href="/subscribe/log?id={{$subscribe->id}}"
										target="_blank">{{$subscribe->times}}</a>
							</td>
							<td> {{$subscribe->updated_at}} </td>
							<td> {{$subscribe->ban_time > 0 ? date('Y-m-d H:i', $subscribe->ban_time): ''}} </td>
							<td> {{$subscribe->ban_desc}} </td>
							<td>
								@if($subscribe->status == 0)
									<button class="btn btn-sm btn-outline-success"
											onclick="setSubscribeStatus('{{$subscribe->id}}', 1)">启用
									</button>
								@endif
								@if($subscribe->status == 1)
									<button class="btn btn-sm btn-outline-danger"
											onclick="setSubscribeStatus('{{$subscribe->id}}', 0)">禁用
									</button>
								@endif
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						共 <code>{{$subscribeList->total()}}</code> 条记录
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{$subscribeList->links()}}
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

		//回车检测
		$(document).on("keypress", "input", function (e) {
			if (e.which === 13) {
				Search();
				return false;
			}
		});

		// 搜索
		function Search() {
			window.location.href = '/subscribe' + '?user_id=' + $("#user_id").val() + '&email=' + $("#email").val() + '&status=' + $("#status option:selected").val();
		}

		// 启用禁用用户的订阅
		function setSubscribeStatus(id, status) {
			$.post("/subscribe/set", {
				_token: '{{csrf_token()}}',
				id: id,
				status: status
			}, function (ret) {
				swal.fire({title: ret.message, timer: 1000, showConfirmButton: false,})
					.then(() => {
						window.location.reload();
					})

			});
		}
	</script>
@endsection
