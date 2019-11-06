@extends('admin.layouts')
@section('css')
	<link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">推送消息列表</h3>
				<div class="panel-actions">
					<button class="btn btn-primary" data-toggle="modal" data-target="#send_modal"><i class="icon wb-plus"></i>推送消息</button>
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
				<div class="alert alert-info alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">×</span>
						<span class="sr-only">Close</span>
					</button>
					仅会推送给关注了您的消息通道的用户 <a class="alert-link" href="{{url('admin/system')}}" target="_blank">设置PushBear</a>.
				</div>
				<table class="table text-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 消息标题</th>
						<th> 消息内容</th>
						<th> 推送状态</th>
						<th> 推送时间</th>
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
						共 {{$list->total()}} 条推送消息
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

	<!-- 推送消息 -->
	<div id="send_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title">推送消息</h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-danger" style="display: none;" id="msg"></div>
					<form action="#" method="post" class="form-horizontal">
						<div class="form-body">
							<div class="form-group">
								<div class="row">
									<label for="title" class="col-md-2 control-label"> 标题 </label>
									<div class="col-md-10">
										<input type="text" class="form-control" name="title" id="title" placeholder="">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<label for="content" class="col-md-2 control-label"> 内容 </label>
									<div class="col-md-10">
										<textarea class="form-control" rows="6" name="content" id="content"></textarea>
										<span class="help-block"> 内容支持<a href="https://maxiang.io/" target="_blank">Markdown语法</a> </span>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button class="btn btn-danger" data-dismiss="modal">取消</button>
					<button class="btn btn-primary" onclick="return send();">推送</button>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
	<script type="text/javascript">
        // 发送通道消息
        function send() {
			const _token = '{{csrf_token()}}';
			const title = $("#title").val();
			const content = $("#content").val();

            if (title.trim() === '') {
                $("#msg").show().html("标题不能为空");
                $("#title").focus();
                return false;
            }

            $.ajax({
                url: '/marketing/addPushMarketing',
                type: "POST",
                data: {_token: _token, title: title, content: content},
                beforeSend: function () {
                    $("#msg").show().html("正在添加...");
                },
                success: function (ret) {
                    if (ret.status === 'fail') {
                        $("#msg").show().html(ret.message);
                        return false;
                    }

                    $("#send_modal").modal("hide");

                },
                error: function () {
                    $("#msg").show().html("请求错误，请重试");
                },
                complete: function () {
                }
            });
        }

        // 关闭modal触发
        $('#send_modal').on('hide.bs.modal', function () {
            window.location.reload();
        });


        function doSearch() {
			const status = $("#status").val();

			window.location.href = "/marketing/pushList?status=" + status;
        }

        function doReset() {
            window.location.href = "/marketing/pushList";
        }
	</script>
@endsection