@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">提现申请详情</h2>
				<div class="panel-actions">
					@if($info->status == -1)
						<span class="badge badge-lg badge-danger"> 已驳回 </span>
					@elseif($info->status == 2)
						<span class="badge badge-lg badge-success"> 已打款 </span>
					@endif
					<a href="/admin/affList" class="btn btn-danger"> 返 回</a>
				</div>
			</div>
			<div class="panel-body">
				<div class="example">
					<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
						<thead class="thead-default">
						<tr>
							<th colspan="6">申请单ID：{{$info->id}} | 申请人：{{$info->user->email}} | 申请提现金额：￥{{$info->amount}}
							                | 申请时间：{{$info->created_at}}</th>
						</tr>
						<tr>
							<th> #</th>
							<th> 关联人</th>
							<th> 关联订单</th>
							<th> 订单金额</th>
							<th> 佣金</th>
							<th> 下单时间</th>
						</tr>
						</thead>
						<tbody>
						@foreach($list as $vo)
							<tr>
								<td> {{$vo->id}} </td>
								<td> {{empty($vo->user) ? '【账号已删除】' : $vo->user->email}} </td>
								<td>
									<a href="/admin/orderList?order_sn={{$vo->order->order_sn}}"
											target="_blank">{{$vo->order->goods->name}}</a>
								</td>
								<td> ￥{{$vo->amount}} </td>
								<td> ￥{{$vo->ref_amount}} </td>
								<td> {{$vo->created_at}} </td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						本申请共涉及 <code>{{$list->total()}}</code> 单
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
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"
			type="text/javascript"></script>
@endsection
