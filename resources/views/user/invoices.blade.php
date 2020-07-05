@extends('user.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading p-20">
				<h1 class="panel-title cyan-600"><i class="icon wb-bookmark"></i>{{trans('home.invoices')}}</h1>
			</div>
			<div class="panel-body">
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> {{trans('home.invoice_table_id')}} </th>
						<th> {{trans('home.invoice_table_name')}} </th>
						<th> {{trans('home.invoice_table_pay_way')}} </th>
						<th> {{trans('home.invoice_table_price')}} </th>
						<th> {{trans('home.invoice_table_create_date')}} </th>
						<th> {{trans('home.invoice_table_expire_at')}} </th>
						<th> {{trans('home.invoice_table_status')}} </th>
						<th> {{trans('home.invoice_table_actions')}} </th>
					</tr>
					</thead>
					<tbody>
					@foreach($orderList as $order)
						<tr>
							<td>{{$loop->iteration}}</td>
							<td><a href="/invoice/{{$order->order_sn}}" target="_blank">{{$order->order_sn}}</a></td>
							<td>{{empty($order->goods) ? ($order->goods_id == 0 ? '余额充值': trans('home.invoice_table_goods_deleted')) : $order->goods->name}}</td>
							<td>{{$order->pay_way === 1 ? trans('home.service_pay_button') : trans('home.online_pay')}}</td>
							<td>￥{{$order->amount}}</td>
							<td>{{$order->created_at}}</td>
							<td>{{empty($order->goods) ? '' : $order->goods_id == 0 || $order->status == 3 ? '' : $order->expire_at}}</td>
							<td>
								@switch($order->status)
									@case(-1)
									<span class="badge badge-default">{{trans('home.invoice_status_closed')}}</span>
									@break
									@case(0)
									<span class="badge badge-danger">{{trans('home.invoice_status_wait_payment')}}</span>
									@break
									@case(1)
									<span class="badge badge-info">{{trans('home.invoice_status_wait_confirm')}}</span>
									@break
									@case(2)
									@if ($order->goods_id == 0)
										<span class="badge badge-default">{{trans('home.invoice_status_payment_confirm')}}</span>
									@else
										@if($order->is_expire)
											<span class="badge badge-default">{{trans('home.invoice_table_expired')}}</span>
										@else
											<span class="badge badge-success">{{trans('home.invoice_table_active')}}</span>
										@endif
									@endif
									@break
									@case(3)
									<span class="badge badge-info">{{trans('home.invoice_table_prepay')}}</span>
							@break
							@endswitch
							<td>
								<div class="btn-group">
									@if($order->status == 0 && $order->payment)
										@if($order->payment->qr_code)
											<a href="/payment/{{$order->payment->trade_no}}" target="_blank" class="btn btn-primary">{{trans('home.pay')}}</a>
										@elseif($order->payment->url)
											<a href="{{$order->payment->url}}" target="_blank" class="btn btn-primary">{{trans('home.pay')}}</a>
										@endif
										<button onclick="closeOrder('{{$order->oid}}')" class="btn btn-danger">{{trans('home.cancel')}}</button>
									@elseif ($order->status == 1)
										<button onClick="window.location.reload();" class="btn btn-primary">
											<i class="icon wb-refresh" aria-hidden="true"></i></button>
									@elseif ($order->status == 3)
										<button onclick="activeOrder('{{$order->oid}}')" class="btn btn-success">{{trans('home.invoice_table_start')}}</button>
									@endif
								</div>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-12">
						<nav class="Page navigation float-right">
							{{$orderList->links()}}
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
		function activeOrder(oid) {
			swal.fire({
				title: '是否提前激活本套餐？',
				html: '套餐激活后：<br>先前套餐将直接失效！<br>过期日期将由本日重新开始计算！',
				type: 'warning',
				showCancelButton: true,
				cancelButtonText: '{{trans('home.ticket_close')}}',
				confirmButtonText: '{{trans('home.ticket_confirm')}}',
			}).then((result) => {
				if (result.value) {
					$.ajax({
						type: "POST",
						url: "/activeOrder",
						async: false,
						data: {_token: '{{csrf_token()}}', oid: oid},
						dataType: 'json',
						success: function (ret) {
							if (ret.status === 'success') {
								swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
									.then(() => window.location.reload())
							} else {
								swal.fire({title: ret.message, type: 'error'});
							}
						}
					});
				}
			})
		}

		function closeOrder(oid) {
			swal.fire({
				title: '关闭订单？',
				type: 'warning',
				showCancelButton: true,
				cancelButtonText: '{{trans('home.ticket_close')}}',
				confirmButtonText: '{{trans('home.ticket_confirm')}}',
			}).then((result) => {
				if (result.value) {
					$.ajax({
						type: "POST",
						url: "payment/close",
						async: false,
						data: {_token: '{{csrf_token()}}', oid: oid},
						dataType: 'json',
						success: function (ret) {
							if (ret.status === 'success') {
								swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
									.then(() => window.location.reload())
							} else {
								swal.fire({title: ret.message, type: 'error'});
							}
						}
					});
				}
			})
		}
	</script>
@endsection
