@extends('user.layouts')
@section('content')
	<div class="page-content">
		<div class="row">
			<div class="col-xxl-2 col-lg-3">
				<div class="card card-shadow">
					<div class="card-block p-20">
						<button type="button" class="btn btn-floating btn-sm btn-pure">
							<i class="icon wb-payment green-500"></i>
						</button>
						<span class="font-weight-400">{{trans('home.account_balance')}}</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{Auth::user()->balance}}</span>
							<br/>
							<button class="btn btn-danger float-right mr-15" data-toggle="modal" data-target="#charge_modal">{{trans('home.recharge')}}</button>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xxl-10 col-lg-9">
				<div class="panel">
					<div class="panel-heading p-20">
						<h1 class="panel-title cyan-700"><i class="icon wb-shopping-cart"></i>{{trans('home.services')}}
						</h1>
					</div>
					<div class="panel-body">
						<div class="row">
							@foreach($goodsList as $key => $goods)
								<div class="col-md-6 col-xl-4 col-xxl-3 pb-30">
									<div class="pricing-list text-left">
										<div class="pricing-header text-white" style="background-color: {{$goods->color}}">
											<div class="pricing-title font-size-20">{{$goods->name}}</div>
											@if($goods->is_limit)
												<div class="ribbon ribbon-vertical ribbon-bookmark ribbon-reverse ribbon-primary mr-10">
													<span class="ribbon-inner h-auto">限<br>购</span>
												</div>
											@elseif($goods->is_hot)
												<div class="ribbon ribbon-vertical ribbon-bookmark ribbon-reverse ribbon-danger mr-10">
													<span class="ribbon-inner h-auto">热<br>销</span>
												</div>
											@endif
											<div class="pricing-price text-white">
												<span class="pricing-currency">¥</span>
												<span class="pricing-amount">{{$goods->price}}</span>
												<span class="pricing-period">/ {{$goods->days}}{{trans('home.day')}}</span>
											</div>
											@if($goods->info)
												<p class="px-30 pb-25 text-center">{{$goods->desc}}</p>
											@endif
										</div>
										<ul class="pricing-features">
											<li>
												@if($goods->type == 2)
													<strong>{{$goods->traffic_label}}</strong> {{trans('home.account_bandwidth_usage')}}/{{trans('home.month')}}
												@elseif($goods->type == 1)
													<strong>{{$goods->traffic_label}}</strong> {{trans('home.account_bandwidth_usage')}}/{{$goods->days}} {{trans('home.day')}}
												@endif
											</li>
											<li>
												<strong>{{trans('home.service_unlimited')}}</strong> {{trans('home.service_device')}}
											</li>
											{!!$goods->info!!}
										</ul>
										<div class="pricing-footer text-center bg-blue-grey-100">
											<a href="/buy/{{$goods->id}}" class="btn btn-lg btn-primary"> {{trans('home.service_buy_button')}}</a>
										</div>
									</div>
								</div>
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="charge_modal" class="modal fade" aria-labelledby="charge_modal" role="dialog" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-simple modal-center">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span></button>
					<h4 class="modal-title">{{trans('home.recharge_balance')}}</h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-danger" id="charge_msg" style="display: none;"></div>
					<form action="#" method="post">
						@if(\App\Components\Helpers::systemConfig()['alipay_qrcode'] || \App\Components\Helpers::systemConfig()['wechat_qrcode'] || !$chargeGoodsList->isEmpty())
							<div class="mb-15 w-p50">
								<select class="form-control" name="charge_type" id="charge_type">
									@if(!$chargeGoodsList->isEmpty() && (\App\Components\Helpers::systemConfig()['is_alipay'] || \App\Components\Helpers::systemConfig()['is_youzan'] || \App\Components\Helpers::systemConfig()['is_f2fpay']))
										<option value="1" selected>{{trans('home.online_pay')}}</option>
									@endif
									@if(\App\Components\Helpers::systemConfig()['alipay_qrcode'] || \App\Components\Helpers::systemConfig()['wechat_qrcode'])
										<option value="2" @if($chargeGoodsList->isEmpty()) selected @endif>二维码</option>
									@endif
									<option value="3">{{trans('home.coupon_code')}}</option>
								</select>
							</div>
						@endif
						@if($chargeGoodsList->isNotEmpty() && (\App\Components\Helpers::systemConfig()['is_alipay'] || \App\Components\Helpers::systemConfig()['is_youzan'] || \App\Components\Helpers::systemConfig()['is_f2fpay']))
							<div class="form-group row" id="charge_balance">
								<label for="online_pay" class="offset-md-2 col-md-2 col-form-label">充值金额</label>
								<div class="col-md-6">
									<select class="form-control round" name="online_pay" id="online_pay">
										@foreach($chargeGoodsList as $goods)
											<option value="{{$goods->id}}">充值{{$goods->price}}元</option>
										@endforeach
									</select>
								</div>
							</div>
						@endif
						@if(\App\Components\Helpers::systemConfig()['alipay_qrcode'] || \App\Components\Helpers::systemConfig()['wechat_qrcode'])
							<div class="text-center" id="charge_qrcode" @if($chargeGoodsList->isNotEmpty() && (\App\Components\Helpers::systemConfig()['is_alipay'] || \App\Components\Helpers::systemConfig()['is_youzan']|| \App\Components\Helpers::systemConfig()['is_f2fpay']))style="display: none;" @endif>
								<div class="row">
									<p class="col-md-12 mb-10">付款时，请
										<mark>备注邮箱账号</mark>
										，充值会在<code>24</code>小时内受理!
									</p>
									@if(\App\Components\Helpers::systemConfig()['wechat_qrcode'])
										<div class="col-md-6">
											<img class="w-p75 mb-10" src="{{\App\Components\Helpers::systemConfig()['wechat_qrcode']}}" alt=""/>
											<p>微 信 | WeChat</p>
										</div>
									@endif
									@if(\App\Components\Helpers::systemConfig()['alipay_qrcode'])
										<div class="col-md-6">
											<img class="w-p75 mb-10" src="{{\App\Components\Helpers::systemConfig()['alipay_qrcode']}}" alt=""/>
											<p>支 付 宝 | AliPay</p>
										</div>
									@endif
								</div>
							</div>
						@endif
						<div class="form-group row" id="charge_coupon_code" @if(\App\Components\Helpers::systemConfig()['alipay_qrcode'] || \App\Components\Helpers::systemConfig()['wechat_qrcode']|| ($chargeGoodsList->isNotEmpty() && (\App\Components\Helpers::systemConfig()['is_alipay'] || \App\Components\Helpers::systemConfig()['is_youzan']|| \App\Components\Helpers::systemConfig()['is_f2fpay']))) style="display: none;" @endif>
							<label for="charge_coupon" class="offset-md-2 col-md-2 col-form-label"> {{trans('home.coupon_code')}} </label>
							<div class="col-md-6">
								<input type="text" class="form-control round" name="charge_coupon" id="charge_coupon" placeholder="{{trans('home.please_input_coupon')}}">
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">{{trans('home.close')}}</button>
					<button type="button" class="btn btn-primary" id="change_btn" onclick="charge()">{{trans('home.recharge')}}</button>
				</div>
			</div>
		</div>
	</div>
@endsection @section('script')
	<script type="text/javascript">
        // 切换充值方式
        $("#charge_type").change(function () {
            if ($(this).val() === '1') {
                $("#charge_balance").show();
                $("#change_btn").show();
                $("#charge_qrcode").hide();
                $("#charge_coupon_code").hide();
            } else if ($(this).val() === '2') {
                $("#charge_balance").hide();
                $("#change_btn").hide();
                $("#charge_qrcode").show();
                $("#charge_coupon_code").hide();
            } else {
                $("#charge_balance").hide();
                $("#charge_qrcode").hide();
                $("#charge_coupon_code").show();
                $("#change_btn").show();
            }
        });

        // 充值
        function charge() {
            const paymentType = $('#charge_type').val();
            const charge_coupon = $('#charge_coupon').val();
            const online_pay = $('#online_pay').val();

            if (paymentType === '1') {
                $("#charge_msg").show().html("正在跳转支付界面");
                window.location.href = '/buy/' + online_pay;
                return false;
            }

            if (paymentType === '3' && charge_coupon.trim() === '') {
                $("#charge_msg").show().html("{{trans('home.coupon_not_empty')}}");
                $("#charge_coupon").focus();
                return false;
            }

            $.ajax({
                type: "POST",
                url: '/charge',
                data: {_token: '{{csrf_token()}}', coupon_sn: charge_coupon},
                beforeSend: function () {
                    $("#charge_msg").show().html("{{trans('home.recharging')}}");
                },
                success: function (ret) {
                    if (ret.status === 'fail') {
                        $("#charge_msg").show().html(ret.message);
                        return false;
                    }

                    $("#charge_modal").modal("hide");
                    window.location.reload();
                },
                error: function () {
                    $("#charge_msg").show().html("{{trans('home.error_response')}}");
                },
                complete: function () {
                }
            });
        }
	</script>
@endsection
