@extends('user.layouts')
@section('css')
	<link href="/assets/examples/css/pages/invoice.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content">
		<div class="panel">
			<div class="panel-body container-fluid">
				<div class="page-invoice-table table-responsive">
					<table class="table table-hover text-md-center">
						@if($goods->type == 3)
							<thead>
							<tr>
								<th class="invoice-title"> {{trans('home.service_name')}} </th>
								<th class="invoice-title"> {{trans('home.service_price')}} </th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td class="p-10">
									<h2>{{$goods->name}}</h2>
									充值金额：{{$goods->price}}元
								</td>
								<td> ￥{{$goods->price}} </td>
							</tr>
							</tbody>
						@else
							<thead>
							<tr>
								<th>{{trans('home.service_name')}}</th>
								<th>{{trans('home.service_desc')}} </th>
								<th>{{trans('home.service_price')}}</th>
								<th>{{trans('home.service_quantity')}}</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td class="text-middle">{{$goods->name}} </td>
								<td>{{trans('home.service_days')}}
									<strong>{{$goods->days}} {{trans('home.day')}}</strong>
									<br>
									@if($goods->type == '2')
										<strong>{{$goods->traffic_label}}</strong> {{trans('home.account_bandwidth_usage')}}/{{trans('home.month')}}
									@else
										<strong>{{$goods->traffic_label}}</strong> {{trans('home.account_bandwidth_usage')}}/{{$goods->days}} {{trans('home.day')}}
									@endif</td>
								<td class="text-middle"> ￥{{$goods->price}} </td>
								<td class="text-middle"> x 1</td>
							</tr>
							</tbody>
						@endif
					</table>
				</div>
				@if($goods->type <= 2)
					<div class="row">
						<div class="col-lg-3 pl-30">
							<div class="input-group">
								<input class="form-control" type="text" name="coupon_sn" id="coupon_sn" placeholder="{{trans('home.coupon')}}"/>
								<div class="input-group-btn">
									<button type="submit" class="btn btn-info" onclick="redeemCoupon()"><i class="icon wb-loop" aria-hidden="true"></i> {{trans('home.redeem_coupon')}}
									</button>
								</div>
							</div>
						</div>
						<div class="col-lg-3 offset-lg-6 text-right">
							<p>{{trans('home.service_subtotal_price')}}
								<span>￥{{$goods->price}}</span>
							</p>
							<p class="page-invoice-amount">{{trans('home.service_total_price')}}
								<span class="grand-total">￥{{$goods->price}}</span>
							</p>
						</div>
						@endif
						<div class="col-md-12 mb-30">
							<div class="float-right">
								@if(\App\Components\Helpers::systemConfig()['is_youzan'])
									<button class="btn btn-lg btn-danger" onclick="onlinePay('0')"> {{trans('home.online_pay')}} </button>
								@elseif(\App\Components\Helpers::systemConfig()['is_alipay'])
									<button class="btn btn-lg btn-success" onclick="onlinePay('4')"> 支付宝扫码</button>
								@elseif(\App\Components\Helpers::systemConfig()['is_f2fpay'])
									<button class="btn btn-lg btn-success" onclick="onlinePay('5')"> 支付宝扫码</button>
								@endif
								@if($goods->type <= 2)
									<button class="btn btn-lg btn-primary" onclick="pay()"> {{trans('home.service_pay_button')}} </button>
								@endif
							</div>
						</div>
					</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script type="text/javascript">
        // 校验优惠券是否可用
        function redeemCoupon() {
            const coupon_sn = $('#coupon_sn').val();
            const goods_price = '{{$goods->price}}';
            $.ajax({
                type: "POST",
                url: "/redeemCoupon",
                async: false,
                data: {_token: '{{csrf_token()}}', coupon_sn: coupon_sn},
                dataType: 'json',
                success: function (ret) {
                    console.log(ret);
                    $(".input-group-prepend").remove();
                    if (ret.status === 'success') {
                        $("#coupon_sn").parent().prepend('<div class="input-group-prepend"><span class="input-group-text bg-green-700"><i class="icon wb-check white" aria-hidden="true"></i></span></div>');
                        // 根据类型计算折扣后的总金额
                        let total_price = 0;
                        if (ret.data.type === 2) {
                            total_price = goods_price * (1 - ret.data.discount / 10);
                            $(".page-invoice-amount").parent().prepend('<p>优惠码 - ' + ret.data.name + ' ' + ret.data.discount + '折<br> 优惠 <span>￥' + total_price.toFixed(2) + '</span></p>');
                            total_price = goods_price - total_price;
                            console.log(total_price);
                        } else {
                            total_price = goods_price - ret.data.amount;
                            total_price = total_price > 0 ? total_price : 0;
                            if (ret.data.type === 1) {
                                $(".page-invoice-amount").parent().prepend('优惠码-' + ret.data.name + ' <span>￥0</span>');
                            }
                        }

                        // 四舍五入，保留2位小数
                        $(".grand-total").text('￥' + total_price.toFixed(2));
                        swal.fire({
                            title: ret.message,
                            type: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        $(".grand-total").text("￥" + goods_price);
                        $("#coupon_sn").parent().prepend('<div class="input-group-prepend"><span class="input-group-text bg-red-700"><i class="icon wb-close white" aria-hidden="true"></i></span></div>');
                        swal.fire({
                            title: ret.message,
                            type: 'error',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                }
            });
        }

        // 在线支付
        function onlinePay(pay_type) {
            const goods_id = '{{$goods->id}}';
            const coupon_sn = $('#coupon_sn').val();
            $.ajax({
                type: "POST",
                url: "/payment/create",
                async: false,
                data: {_token: '{{csrf_token()}}', goods_id: goods_id, coupon_sn: coupon_sn, pay_type: pay_type},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({
                            title: ret.message,
                            type: 'success',
                            timer: 1300,
                            showConfirmButton: false
                        });
                        if (pay_type === 4) {
                            // 如果是Alipay支付写入Alipay的支付页面
                            document.body.innerHTML += ret.data;
                            document.forms['alipaysubmit'].submit();
                        } else {
                            window.location.href = '/payment/' + ret.data;
                        }
                    } else {
                        swal.fire({
                            title: ret.message,
                            type: 'error',
                        })
                    }
                },
                error: function () {
                    swal.fire('未知错误', '请开工单通知客服', 'error')
                }
            });
        }

        // 余额支付
        function pay() {
            const goods_id = '{{$goods->id}}';
            const coupon_sn = $('#coupon_sn').val();
            $.ajax({
                type: "POST",
                url: "/buy/" + goods_id,
                async: false,
                data: {_token: '{{csrf_token()}}', coupon_sn: coupon_sn},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success',})
                            .then(() => window.location.href = '/invoices')
                    } else {
                        swal.fire({title: ret.message, type: 'error',})
                    }
                },
                error: function () {
                    swal.fire({title: '未知错误！请开工单通知客服', type: 'error',})
                }
            });
        }
	</script>
@endsection
