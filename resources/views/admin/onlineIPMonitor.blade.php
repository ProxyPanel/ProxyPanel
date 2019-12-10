@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">在线IP监控
					<small>实时</small>
				</h3>
			</div>
			<div class="panel-body">
				<div class="form-row">
					<div class="form-group col-lg-2 col-sm-2">
						<input type="number" class="form-control" name="id" id="id" value="{{Request::get('id')}}" placeholder="ID"/>
					</div>
					<div class="form-group col-lg-2 col-sm-5">
						<input type="text" class="form-control" name="username" id="username" value="{{Request::get('username')}}" placeholder="用户名"/>
					</div>
					<div class="form-group col-lg-2 col-sm-5">
						<input type="text" class="form-control" name="ip" id="ip" value="{{Request::get('ip')}}" placeholder="IP"/>
					</div>
					<div class="form-group col-lg-2 col-sm-3">
						<input type="number" class="form-control" name="port" id="port" value="{{Request::get('port')}}" placeholder="端口"/>
					</div>
					<div class="form-group col-lg-2 col-sm-5">
						<select name="nodeId" id="nodeId" class="form-control" onChange="Search()">
							<option value="" @if(Request::get('nodeId') == '') selected @endif>选择节点</option>
							@foreach($nodeList as $node)
								<option value="{{$node->id}}" @if(Request::get('nodeId') == $node->id) selected @endif>{{$node->name}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group col-lg-2 col-sm-4 btn-group">
						<button class="btn btn-primary" onclick="Search()">搜索</button>
						<a href="/admin/onlineIPMonitor" class="btn btn-danger">重置</a>
					</div>
				</div>
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 时间</th>
						<th> 类型</th>
						<th> 节点</th>
						<th> 用户</th>
						<th> 地址</th>
						<th> IP</th>
						<th> 归属地</th>
					</tr>
					</thead>
					<tbody>
					@if ($list->isEmpty())
						<tr>
							<td colspan="8">暂无数据</td>
						</tr>
					@else
						@foreach($list as $vo)
							<tr>
								<td>{{$vo->id}}</td>
								<td>{{$vo->created_at}}</td>
								<td>{{$vo->type}}</td>
								<td>{{$vo->node ? $vo->node->name : '【节点已删除】'}}</td>
								<td>{{$vo->user ? $vo->user->username : '【用户已删除】'}}</td>
								<td>{{$vo->user ? $vo->user->address : '【用户已删除】'}}</td>
								<td>
									@if (strpos($vo->ip, ',') == TRUE)
										@foreach (explode(',', $vo->ip) as $ip)
											<a href="https://www.ipip.net/ip/{{$ip}}.html" target="_blank">{{$ip}}</a>
										@endforeach
									@else
										<a href="https://www.ipip.net/ip/{{$vo->ip}}.html" target="_blank">{{$vo->ip}}</a>
									@endif
								</td>
								<td>{{strpos($vo->ip, ',') == TRUE? '':$vo->ipInfo}}</td>
							</tr>
						@endforeach
					@endif
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						共 <code>{{$list->total()}}</code> 个账号
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
	<script src="/assets/custom/Plugin/clipboardjs/clipboard.min.js" type="text/javascript"></script>
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
            window.location.href = '/admin/onlineIPMonitor?id=' + $("#id").val() + '&ip=' + $("#ip").val() + '&username=' + $("#username").val() + '&port=' + $("#port").val() + '&nodeId=' + $("#nodeId option:selected").val();
        }
	</script>
@endsection
