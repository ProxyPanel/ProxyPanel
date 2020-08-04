@extends('admin.layouts')
@section('css')
	<link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">用户在线IP列表
					<small>最近10分钟</small>
				</h3>
			</div>
			<div class="panel-body">
				<div class="form-inline pb-20">
					<div class="form-group">
						<input type="text" class="form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名">
					</div>
					<div class="form-group">
						<input type="text" class="form-control w-80" name="wechat" value="{{Request::get('wechat')}}" id="wechat" placeholder="微信">
					</div>
					<div class="form-group">
						<input type="text" class="form-control w-80" name="qq" value="{{Request::get('qq')}}" id="qq" placeholder="QQ">
					</div>
					<div class="form-group">
						<input type="text" class="form-control w-60" name="port" value="{{Request::get('port')}}" id="port" placeholder="端口">
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
						<th> 端口</th>
						<th> 状态</th>
						<th> 代理</th>
						<th> 连接IP</th>
					</tr>
					</thead>
					<tbody>
					@if ($userList->isEmpty())
						<tr>
							<td colspan="6">暂无数据</td>
						</tr>
					@else
						@foreach ($userList as $user)
							<tr>
								<td> {{$user->id}} </td>
								<td> {{$user->username}} </td>
								<td> {{$user->port}} </td>
								<td>
									@if ($user->status > 0)
										<span class="badge badge-lg badge-success">正常</span>
									@elseif ($user->status < 0)
										<span class="badge badge-lg badge-danger">禁用</span>
									@else
										<span class="badge badge-lg badge-default">未激活</span>
									@endif
								</td>
								<td>
									@if ($user->enable)
										<span class="badge badge-lg badge-success">启用</span>
									@else
										<span class="badge badge-lg badge-danger">禁用</span>
									@endif
								</td>
								<td>
									@if(!$user->onlineIPList->isEmpty())
										<table class="text-center" data-toggle="table" data-mobile-responsive="true">
											<thead>
											<tr>
												<th> 时间</th>
												<th> 节点</th>
												<th> 类型</th>
												<th> IP</th>
											</tr>
											</thead>
											<tbody>
											@foreach($user->onlineIPList as $vo)
												<tr>
													<td>{{$vo->created_at}}</td>
													<td>{{$vo->node ? $vo->node->name : '【节点已删除】'}}</td>
													<td>{{$vo->type}}</td>
													<td><a href="https://www.ipip.net/ip/{{$vo->ip}}.html" target="_blank">{{$vo->ip}}</a></td>
												</tr>
											@endforeach
											</tbody>
										</table>
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
						共 {{$userList->total()}} 个账号
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{ $userList->links() }}
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
        function doSearch() {
            var username = $("#username").val();
            var wechat = $("#wechat").val();
            var qq = $("#qq").val();
            var port = $("#port").val();

            window.location.href = '/admin/userOnlineIPList' + '?username=' + username + '&wechat=' + wechat + '&qq=' + qq + '&port=' + port;
        }

        // 重置
        function doReset() {
            window.location.href = '/admin/userOnlineIPList';
        }
	</script>
@endsection