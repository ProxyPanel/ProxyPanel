@extends('admin.layouts')
@section('css')
	<link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">邮件群发列表</h3>
				<div class="panel-actions">
					<button class="btn btn-primary" onclick="send()"><i class="icon wb-envelope"></i>群发邮件</button>
				</div>
			</div>
			<div class="panel-body">
				<div class="form-inline pb-20">
					<div class="form-group">
						<select class="form-control" name="status" id="status">
							<option value="" @if(Request::get('status') == '') selected hidden @endif>状态</option>
							<option value="0" @if(Request::get('status') == '0') selected hidden @endif>待发送</option>
							<option value="-1" @if(Request::get('status') == '-1') selected hidden @endif>失败</option>
							<option value="1" @if(Request::get('status') == '1') selected hidden @endif>成功</option>
						</select>
					</div>
					<div class="btn-group">
						<button class="btn btn-primary" onclick="doSearch()">搜索</button>
						<button class="btn btn-danger" onclick="doReset()">重置</button>
					</div>
				</div>
				<table class="table text-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 消息标题</th>
						<th> 消息内容</th>
						<th> 发送状态</th>
						<th> 发送时间</th>
						<th> 错误信息</th>
					</tr>
					</thead>
					<tbody>
					@if ($list->isEmpty())
						<tr>
							<td colspan="6">暂无数据</td>
						</tr>
					@else
						@foreach($list as $vo)
							<tr>
								<td> {{$vo->id}} </td>
								<td> {{$vo->title}} </td>
								<td> {{$vo->content}} </td>
								<td> {{$vo->status_label}} </td>
								<td> {{$vo->created_at}} </td>
								<td> {{$vo->error}} </td>
							</tr>
						@endforeach
					@endif
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-md-4 col-sm-4">
						共 {{$list->total()}} 条消息
					</div>
					<div class="col-md-8 col-sm-8">
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
        // 发送邮件
        function send() {
			swal.fire('抱歉','由于作者闭源，开发无限期延期','info');
        }

		function doSearch() {
			const status = $("#status option:selected").val();

			window.location.href = "/marketing/emailList?status=" + status;
		}

		function doReset() {
			window.location.href = "/marketing/emailList";
		}
	</script>
@endsection