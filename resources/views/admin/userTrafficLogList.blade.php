@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">流量变动记录</h3>
			</div>
			<div class="panel-body">
				<div class="form-row">
					<div class="form-group col-lg-4 col-sm-6">
						<input type="text" class="form-control" name="username" id="username" value="{{Request::get('username')}}" placeholder="用户名"/>
					</div>
					<div class="form-group col-lg-2 col-sm-6 btn-group">
						<button class="btn btn-primary" onclick="Search()">搜索</button>
						<a href="/admin/userTrafficLogList" class="btn btn-danger">重置</a>
					</div>
				</div>
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 用户名</th>
						<th> 订单</th>
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
								<td>
									@if ($vo->order_id)
										@if($vo->order)
											<a href="/admin/orderList?oid={{$vo->order_id}}">{{$vo->order->goods->name}}</a>
										@else
											【订单已删除】
										@endif
									@endif
								</td>
								<td> {{$vo->before}} </td>
								<td> {{$vo->after}} </td>
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
						共 <code>{{$list->total()}}</code> 条记录
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
        //回车检测
        $(document).on("keypress", "input", function (e) {
            if (e.which === 13) {
                Search();
                return false;
            }
        });

        // 搜索
        function Search() {
            const username = $("#username").val();
            window.location.href = '/admin/userTrafficLogList' + '?username=' + username;
        }
	</script>
@endsection
