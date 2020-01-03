@extends('user.layouts')
@section('content')
	<div class="page-content container">
		<div class="panel panel-bordered">
			<div class="panel-heading">
				<h1 class="panel-title cyan-600"><i class="icon wb-payment"></i>{{\App\Components\Helpers::systemConfig()['website_name']}}{{trans('home.online_pay')}}
				</h1>
			</div>
			<div class="panel-body border-primary">
				<div class="row">
					<div class="col-2"></div>
					<div class="alert alert-info col-8 text-center">
						请使用<strong class="red-600"> 支付宝 </strong>扫描二维码进行支付
					</div>
					<div class="col-2"></div>
					<div class="col-2"></div>
					<div class="row col-8">
						<div class="col-md-6">
							<ul class="list-group list-group-dividered">
								<li class="list-group-item">服务名称：{{$payment->order->goods->name}}</li>
								<li class="list-group-item">支付金额：{{$payment->amount}}元</li>
								<li class="list-group-item">有效期：{{$payment->order->goods->days}} 天</li>
								<li class="list-group-item"> 请在<code>15分钟</code>内完成支付，否者订单将会自动关闭</li>
							</ul>
						</div>
						<div class="col-md-6 text-center mb-15">
							<img class="h-250 w-250" src="{{$payment->qr_local_url}}" alt="支付二维码">
						</div>
					</div>
					<div class="col-2"></div>
					<div class="col-2"></div>
					<div class="alert alert-danger col-8 text-center">
						<strong>手机用户</strong>：长按二维码 -> 保存图片 ->打开支付软件 -> 扫一扫 -> 选择相册 进行付款
					</div>
					<div class="col-2"></div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script type="text/javascript">
        $(document).ready(function () {
            // 支付宝直接跳转支付
			@if(\App\Components\Helpers::systemConfig()['is_alipay'])
                document.body.innerHTML += unescapeHTML("{{$payment->qr_code}}");
            document.forms['alipaysubmit'].submit();
			@endif
            getStatus();
        });

        // 检查支付单状态
        function getStatus() {
            $.get("/payment/getStatus", {sn: '{{$payment->sn}}'}, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.href = '/invoices')
                } else if (ret.status === 'error') {
                    swal.fire({title: ret.message, type: "error", timer: 1000, showConfirmButton: false})
                        .then(() => window.location.href = '/invoices')
                } else {
                    // 无结果时，每2秒查询一次订单状态
                    setInterval("getStatus()", 2000);
                }
            });
        }

        // 还原html脚本 < > & " '
        function unescapeHTML(str) {
            str = "" + str;
            return str.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&amp;/g, "&").replace(/&quot;/g, '"').replace(/&#039;/g, "'");
        }
	</script>
@endsection