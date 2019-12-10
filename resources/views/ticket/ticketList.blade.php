@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">工单列表</h3>
				<div class="panel-actions">
					<a href="/ticket/addTicket" class="btn btn-primary btn-animate btn-animate-side">
						<span><i class="icon wb-plus" aria-hidden="true"></i> {{trans('home.ticket_table_new_button')}}</span>
					</a>
				</div>
			</div>
			<div class="panel-body">
				<div class="form-row">
					<div class="form-group col-lg-3 col-sm-6">
						<input type="text" class="form-control" name="username" id="username" value="{{Request::get('username')}}" placeholder="用户名" autocomplete="off"/>
					</div>
					<div class="form-group col-lg-2 col-sm-6 btn-group">
						<button class="btn btn-primary" onclick="Search()">搜索</button>
						<a href="/ticket/ticketList" class="btn btn-danger">重置</a>
					</div>
				</div>
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 用户名</th>
						<th> 标题</th>
						<th> 状态</th>
					</tr>
					</thead>
					<tbody>
					@if($ticketList->isEmpty())
						<tr>
							<td colspan="4">暂无数据</td>
						</tr>
					@else
						@foreach($ticketList as $ticket)
							<tr>
								<td> {{$ticket->id}} </td>
								<td>
									@if(!$ticket->user)
										【账号已删除】
									@else
										<a href="/admin/userList?id={{$ticket->user->id}}" target="_blank">{{$ticket->user->username}}</a>
									@endif
								</td>

								<td>
									<a href="/ticket/replyTicket?id={{$ticket->id}}" target="_blank">{{$ticket->title}}</a>
								</td>
								<td>
									{!!$ticket->status_label!!}
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
						共 <code>{{$ticketList->total()}}</code> 个工单
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{$ticketList->links()}}
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
            window.location.href = '/ticket/ticketList?username=' + $("#username").val();
        }
	</script>
@endsection
