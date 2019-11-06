@extends('admin.layouts')
@section('css')
	<link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">返利流水记录</h2>
			</div>
			<div class="panel-body">
				<div class="form-inline pb-20">
					<div class="form-group">
						<input type="text" class="form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="消费者">
					</div>
					<div class="form-group">
						<input type="text" class="form-control" name="ref_username" value="{{Request::get('ref_username')}}" id="ref_username" placeholder="邀请人">
					</div>
					<div class="form-group">
						<select  name="status" id="status" class="form-control">
							<option value="" @if(Request::get('status') == '') selected hidden @endif>状态</option>
							<option value="0" @if(Request::get('status') == '0') selected hidden @endif>未提现</option>
							<option value="1" @if(Request::get('status') == '1') selected hidden @endif>申请中</option>
							<option value="2" @if(Request::get('status') == '2') selected hidden @endif>已提现</option>
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
						<th> 消费者</th>
						<th> 邀请者</th>
						<th> 订单号</th>
						<th> 消费金额</th>
						<th> 返利金额</th>
						<th> 生成时间</th>
						<th> 处理时间</th>
						<th> 状态</th>
					</tr>
					</thead>
					<tbody>
					@if($list->isEmpty())
						<tr>
							<td colspan="9">暂无数据</td>
						</tr>
					@else
						@foreach($list as $vo)
							<tr>
								<td> {{$vo->id}} </td>
								<td>
									@if(empty($vo->user))
										【账号已删除】
									@else
										<a href="/admin/userRebateList?username={{$vo->user->username}}"> {{$vo->user->username}} </a>
									@endif
								</td>
								<td>
									@if(empty($vo->ref_user))
										【账号已删除】
									@else
										<a href="/admin/userRebateList?ref_username={{$vo->ref_user->username}}"> {{$vo->ref_user->username}} </a>
									@endif
								</td>
								<td> {{$vo->order_id}} </td>
								<td> {{$vo->amount}} </td>
								<td> {{$vo->ref_amount}} </td>
								<td> {{$vo->created_at}} </td>
								<td> {{$vo->updated_at}} </td>
								<td>
									@if ($vo->status == 1)
										<span class="badge badge-danger">申请中</span>
									@elseif($vo->status == 2)
										<span class="badge badge-default">已提现</span>
									@else
										<span class="badge badge-info">未提现</span>
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
						共 {{$list->total()}} 个申请
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
        function doSearch() {
            var username = $("#username").val();
            var ref_username = $("#ref_username").val();
            var status = $("#status option:selected").val();

            window.location.href = '/admin/userRebateList' + '?username=' + username + '&ref_username=' + ref_username + '&status=' + status;
        }

        // 重置
        function doReset() {
            window.location.href = '/admin/userRebateList';
        }
	</script>
@endsection