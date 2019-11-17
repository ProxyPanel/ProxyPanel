@extends('admin.layouts')

@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">流量日志</h2>
			</div>
			<div class="panel-body">
				<div class="form-row">
					<div class="form-group col-lg-2 col-sm-4">
						<input type="number" class="form-control" name="user_id" id="user_id" value="{{Request::get('user_id')}}" placeholder="用户ID"/>
					</div>
					<div class="form-group col-lg-3 col-sm-8">
						<input type="text" class="form-control" name="username" id="username" value="{{Request::get('username')}}" placeholder="用户名"/>
					</div>
					<div class="form-group col-lg-2 col-sm-3">
						<input type="number" class="form-control" name="port" id="port" value="{{Request::get('port')}}" placeholder="端口"/>
					</div>
					<div class="form-group col-lg-3 col-sm-5">
						<select class="form-control" name="nodeId" id="nodeId" onChange="Search()">
							<option value="" @if(Request::get('nodeId') == '') selected @endif>选择节点</option>
							@foreach($nodeList as $node)
								<option value="{{$node->id}}" @if(Request::get('nodeId') == $node->id) selected @endif>{{$node->name}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group col-lg-2 col-sm-4 btn-group">
						<button class="btn btn-primary" onclick="Search()">搜索</button>
						<button href="/admin/trafficLog" class="btn btn-danger">重置</button>
					</div>
				</div>
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 用户</th>
						<th> 节点</th>
						<th> 流量比例</th>
						<th> 上传流量</th>
						<th> 下载流量</th>
						<th> 总流量</th>
						<th> 记录时间</th>
					</tr>
					</thead>
					<tbody>
					@if($list->isEmpty())
						<tr>
							<td colspan="8">暂无数据</td>
						</tr>
					@else
						@foreach($list as $vo)
							<tr>
								<td> {{$vo->id}} </td>
								<td>
									@if(empty($vo->user))
										【账号已删除】
									@else
										<a href="/admin/userList?id={{$vo->user->id}}" target="_blank"> {{$vo->user->username}} </a>
									@endif
								</td>
								<td> {{$vo->node ? $vo->node->name : '【节点已删除】'}} </td>
								<td> {{$vo->rate}} </td>
								<td> {{$vo->u}} </td>
								<td> {{$vo->d}} </td>
								<td><span class="badge badge-danger"> {{$vo->traffic}} </span></td>
								<td> {{$vo->log_time}} </td>
							</tr>
						@endforeach
					@endif
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-6">
						共 <code>{{$list->total()}} 条记录</code>，合计 <code>{{$totalTraffic}}</code>
					</div>
					<div class="col-sm-6">
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
            const port = $("#port").val();
            const user_id = $("#user_id").val();
            const username = $("#username").val();
            const nodeId = $("#nodeId option:selected").val();
            window.location.href = '/admin/trafficLog' + '?port=' + port + '&user_id=' + user_id + '&username=' + username + '&nodeId=' + nodeId;
        }
	</script>
@endsection
