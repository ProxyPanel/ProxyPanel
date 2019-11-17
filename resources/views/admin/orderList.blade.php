@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">订单列表</h2>
			</div>
			<div class="panel-body">
				<div class="form-row">
					<div class="form-group col-lg-2 col-sm-6">
						<input type="text" class="form-control" name="username" id="username" value="{{Request::get('username')}}" placeholder="用户名"/>
					</div>
					<div class="form-group col-lg-2 col-sm-6">
						<input type="number" class="form-control" name="order_sn" id="order_sn" value="{{Request::get('order_sn')}}" placeholder="订单号"/>
					</div>
					<div class="form-group col-lg-6 col-sm-12">
						<div class="input-group input-daterange" data-plugin="datepicker">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
							</div>
							<input type="text" class="form-control" name="start" id="start" value="" placeholder="{{date("Y-m-d")}}"/>
							<div class="input-group-prepend">
								<span class="input-group-text">至</span>
							</div>
							<input type="text" class="form-control" name="end" id="end" value="" placeholder="{{date("Y-m-d",strtotime("+1 month"))}}"/>
						</div>
					</div>
					<div class="form-group col-lg-2 col-sm-6">
						<select class="form-control" name="is_expire" id="is_expire" onChange="Search()">
							<option value="" @if(Request::get('is_expire') == '') selected hidden @endif>是否过期</option>
							<option value="0" @if(Request::get('is_expire') == '0') selected hidden @endif>否</option>
							<option value="1" @if(Request::get('is_expire') == '1') selected hidden @endif>是</option>
						</select>
					</div>
					<div class="form-group col-lg-2 col-sm-6">
						<select class="form-control" name="is_coupon" id="is_coupon" onChange="Search()">
							<option value="" @if(Request::get('is_coupon') == '') selected hidde @endif>是否使用优惠券</option>
							<option value="0" @if(Request::get('is_coupon') == '0') selected hidden @endif>否</option>
							<option value="1" @if(Request::get('is_coupon') == '1') selected hidden @endif>是</option>
						</select>
					</div>
					<div class="form-group col-lg-2 col-sm-6">
						<select class="form-control" name="pay_way" id="pay_way" onChange="Search()">
							<option value="" @if(Request::get('pay_way') == '') selected hidden @endif>支付方式</option>
							<option value="1" @if(Request::get('pay_way') == '1') selected hidden @endif>余额支付</option>
							<option value="2" @if(Request::get('pay_way') == '2') selected hidden @endif>有赞云支付</option>
							<option value="4" @if(Request::get('pay_way') == '4') selected hidden @endif>支付宝国际</option>
							<option value="5" @if(Request::get('pay_way') == '5') selected hidden @endif>支付宝当面付</option>
						</select>
					</div>
					<div class="form-group col-lg-2 col-sm-6">
						<select class="form-control" name="status" id="status" onChange="Search()">
							<option value="" @if(Request::get('status') == '') selected hidden @endif>订单状态</option>
							<option value="-1" @if(Request::get('status') == '-1') selected hidden @endif>已关闭</option>
							<option value="0" @if(Request::get('status') == '0') selected hidden @endif>待支付</option>
							<option value="1" @if(Request::get('status') == '1') selected hidden @endif>已支付待确认</option>
							<option value="2" @if(Request::get('status') == '2') selected hidden @endif>已完成</option>
						</select>
					</div>
					<div class="form-group col-lg-3 col-sm-6">
						<div class="d-flex align-items-center">
							<div class="radio-custom radio-primary radio-inline">
								<input type="radio" name="sort" value="0" checked/>
								<label for="type">降序</label>
							</div>
							<div class="radio-custom radio-primary radio-inline">
								<input type="radio" name="sort" value="1" @if(Request::get('sort') == '1') checked @endif/>
								<label for="type">升序</label>
							</div>
						</div>
					</div>
					<div class="form-group col-lg-2 col-sm-6 btn-group">
						<button class="btn btn-primary" onclick="Search()">搜索</button>
						<a href="/admin/orderList" class="btn btn-danger">重置</a>
					</div>
				</div>
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 用户名</th>
						<th> 订单号</th>
						<th> 商品</th>
						<th> 过期时间</th>
						<th> 优惠券</th>
						<th> 原价</th>
						<th> 实价</th>
						<th> 支付方式</th>
						<th> 订单状态</th>
						<th> 创建时间</th>
					</tr>
					</thead>
					<tbody>
					@if($orderList->isEmpty())
						<tr>
							<td colspan="11">暂无数据</td>
						</tr>
					@else
						@foreach($orderList as $order)
							<tr>
								<td> {{$order->oid}} </td>
								<td>
									@if(empty($order->user) )
										【账号不存在】
									@else
										<a href="/admin/userList?id={{$order->user->id}}" target="_blank">{{$order->user->username}} </a>
									@endif
								</td>
								<td> {{$order->order_sn}}</td>
								<td> {{$order->goods->name}} </td>
								<td> {{$order->is_expire ? '已过期' : $order->expire_at}} </td>
								<td> {{$order->coupon ? $order->coupon->name . ' - ' . $order->coupon->sn : ''}} </td>
								<td> ￥{{$order->origin_amount}} </td>
								<td> ￥{{$order->amount}} </td>
								<td>
									@if($order->pay_way == '1')
										<span class="badge badge-lg badge-info"> 余额支付 </span>
									@elseif($order->pay_way == '2')
										<span class="badge badge-lg badge-info"> 有赞云支付 </span>
									@elseif($order->pay_way == '4')
										<span class="label label-info"> 支付宝国际 </span>
									@elseif($order->pay_way == '5')
										<span class="label label-info"> 支付宝当面付 </span>
									@else
										<span class="badge badge-lg badge-info"> 未知 </span>
									@endif
								</td>
								<td>
									@if($order->status == '-1')
										<span class="badge badge-lg badge-danger"> 已关闭 </span>
									@elseif ($order->status == '0')
										<span class="badge badge-lg badge-default"> 待支付 </span>
									@elseif ($order->status == '1')
										<span class="badge badge-lg badge-default"> 已支付待确认 </span>
									@else
										<span class="badge badge-lg badge-success"> 已完成 </span>
									@endif
								</td>
								<td> {{$order->created_at}} </td>
							</tr>
						@endforeach
					@endif
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						共 <code>{{$orderList->total()}}</code> 个订单
					</div>
					<div class="col-sm-8">
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
	<script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/bootstrap-datepicker.js" type="text/javascript"></script>
	<script type="text/javascript">
        // 有效期
        $('.input-daterange').datepicker({
            format: "yyyy-mm-dd",
        });
        //回车检测
        $(document).on("keypress", "input", function (e) {
            if (e.which === 13) {
                Search();
                return false;
            }
        });

        // 搜索
        function Search() {
            const username = $("#username").val();
            const order_sn = $("#order_sn").val();
            const is_expire = $("#is_expire").val();
            const is_coupon = $("#is_coupon").val();
            const pay_way = $("#pay_way").val();
            const status = $("#status").val();
            const sort = $("input:radio[name='sort']:checked").val();
            const startTime = $("#start").val();
            const endTime = $("#end").val();
            const range_time = [startTime, endTime];
            window.location.href = '/admin/orderList?username=' + username + '&order_sn=' + order_sn + '&is_expire=' + is_expire + '&is_coupon=' + is_coupon + '&pay_way=' + pay_way + '&status=' + status + '&sort=' + sort + '&range_time=' + range_time;
        }
	</script>
@endsection
