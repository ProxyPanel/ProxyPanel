@extends('admin.layouts')

@section('css')
	<link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">流量变动记录</h3>
			</div>
			<div class="panel-body">
				<div class="form-inline pb-20">
					<div class="form-group">
						<input type="text" class="form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名">
					</div>
					<div class="btn-group">
						<button class="btn btn-primary" onclick="doSearch()">搜索</button>
						<button class="btn btn-danger" onclick="doReset()">重置</button>
					</div>
				</div>
				<table class="text-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 用户名</th>
						<th> 订单ID</th>
						<th> 变动前流量</th>
						<th> 变动后流量</th>
						<th> 描述</th>
						<th> 发生时间</th>
					</tr>
					</thead>
					<tbody>
					@if($list->isEmpty())
						<tr>
							<td colspan="7">暂无数据</td>
						</tr>
					@else
						@foreach($list as $vo)
							<tr>
								<td> {{$vo->id}} </td>
								<td>
									@if(empty($vo->user))
										【账号已删除】
									@else
										<a href="/admin/userTrafficLogList?username={{$vo->user->username}}"> {{$vo->user->username}} </a>
									@endif
								</td>
								<td> {{$vo->order_id ? ($vo->order ? $vo->order->goods->name : '【订单已删除】') : ''}} </td>
								<td> {{$vo->before}} </td>
								<td> {{$vo->after}} </td>
								<td> {{$vo->desc}} </td>
								<td> {{$vo->desc}} </td>
								<td> {{$vo->created_at}} </td>
							</tr>
						@endforeach
					@endif
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						共 {{$list->total()}} 条记录
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{ $list->links() }}
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
	<script type="text/javascript">
        // 搜索
        function do_search() {
            var username = $("#username").val();

            window.location.href = '/admin/userTrafficLogList' + '?username=' + username;
        }

        // 重置
        function do_reset() {
            window.location.href = '/admin/userTrafficLogList';
        }
	</script>
@endsection