@extends('admin.layouts')
@section('css')
	<link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">邮件投递记录</h2>
			</div>
			<div class="panel-body">
				<div class="form-inline mb-20">
					<div class="form-group">
						<select class="form-control" name="type" id="type" onChange="doSearch()">
							<option value="" @if(Request::get('type') == '') selected hidden @endif>类型</option>
							<option value="1" @if(Request::get('type') == '1') selected hidden @endif>邮件</option>
							<option value="2" @if(Request::get('type') == '2') selected hidden @endif>ServerChan</option>
							<option value="3" @if(Request::get('type') == '3') selected hidden @endif>Bark</option>
							<option value="4" @if(Request::get('type') == '4') selected hidden @endif>Telegram</option>
						</select>
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
						<th> 类型</th>
						<th> 识别码 </th>
						<th> 收信地址</th>
						<th> 标题</th>
						<th> 内容</th>
						<th> 投递时间</th>
						<th> 投递状态</th>
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
								<td> {{$vo->type == 1 ? 'Email' : 'serverChan'}} </td>
								<td> @if($vo->type == 3) <a href="/b/{{$vo->code}}" target="_blank">{{$vo->code}}</a> @endif </td>
								<td> {{$vo->address}} </td>
								<td> {{$vo->title}} </td>
								<td> {{$vo->content}} </td>
								<td> {{$vo->created_at}} </td>
								<td>
									@if($vo->status < 0)
										<span class="badge badge-danger"> {{str_limit($vo->error)}} </span>
									@elseif($vo->status > 0)
										<labe class="badge badge-success">投递成功</labe>
									@else
										<span class="badge badge-default"> 等待投递 </span>
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
						共 {{$list->total()}} 条记录
					</div>
					<div class="col-sm-8">
						<div class="Page navigation float-right">
							{{ $list->links() }}
						</div>
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
			const type = $("#type option:selected").val();

			window.location.href = '/admin/emailLog?type=' + type;
		}

		// 重置
		function doReset() {
			window.location.href = '/admin/emailLog';
		}
	</script>
@endsection