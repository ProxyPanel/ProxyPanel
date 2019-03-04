@extends('user.layouts')
@section('css')
    <link href="/assets/pages/css/invoice-2.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="invoice-content-2 bordered">
            <div class="row invoice-body">
                <div class="col-xs-12 table-responsive">
                    <table class="table table-hover">
                        @if($goods->type == 3)
                            <thead>
                                <tr>
                                    <th class="invoice-title"> {{trans('home.service_name')}} </th>
                                    <th class="invoice-title text-center"> {{trans('home.service_price')}} </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 10px;">
                                        <h2>{{$goods->name}}</h2>
                                        充值金额：{{$goods->price}}元
                                        </td>
                                    <td class="text-center"> ￥{{$goods->price}} </td>
                                </tr>
                            </tbody>
                        @else
                            <thead>
                                <tr>
                                    <th class="invoice-title"> {{trans('home.service_name')}} </th>
                                    <th class="invoice-title text-center"> {{trans('home.service_price')}} </th>
                                    <th class="invoice-title text-center"> {{trans('home.service_quantity')}} </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 10px;">
                                        <h2>{{$goods->name}}</h2>
                                        {{trans('home.service_traffic')}} {{$goods->traffic_label}}
                                        <br/>
                                        {{trans('home.service_days')}} {{$goods->days}} {{trans('home.day')}}
                                    </td>
                                    <td class="text-center"> ￥{{$goods->price}} </td>
                                    <td class="text-center"> x 1 </td>
                                </tr>
                            </tbody>
                      	@endif
                    </table>
                </div>
            </div>
            @if($goods->type <= 2)
                <div class="row invoice-subtotal">
                    <div class="col-xs-3">
                        <h2 class="invoice-title"> {{trans('home.service_subtotal_price')}} </h2>
                        <p class="invoice-desc"> ￥{{$goods->price}} </p>
                    </div>
                    <div class="col-xs-3">
                        <h2 class="invoice-title"> {{trans('home.service_total_price')}} </h2>
                        <p class="invoice-desc grand-total"> ￥{{$goods->price}} </p>
                    </div>
                    <div class="col-xs-6">
                        <h2 class="invoice-title"> {{trans('home.coupon')}} </h2>
                        <p class="invoice-desc">
                            <div class="input-group">
                                <input class="form-control" type="text" name="coupon_sn" id="coupon_sn" placeholder="{{trans('home.coupon')}}" />
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="redeemCoupon()"><i class="fa fa-refresh"></i> {{trans('home.redeem_coupon')}} </button>
                                </span>
                            </div>
                        </p>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-xs-12" style="text-align: right;">
                    @if(\App\Components\Helpers::systemConfig()['is_youzan'])
                        <a class="btn btn-lg red hidden-print" onclick="onlinePay(0)"> {{trans('home.online_pay')}} </a>
                    @elseif(\App\Components\Helpers::systemConfig()['is_alipay'])
                        <a class="btn btn-lg green hidden-print" onclick="onlinePay(4)"> 支付宝扫码 </a>
                    @elseif(\App\Components\Helpers::systemConfig()['is_f2fpay'])
                        <a class="btn btn-lg green hidden-print" onclick="onlinePay(5)"> 支付宝扫码 </a>
                    @endif
                  	@if($goods->type <= 2)
                        <a class="btn btn-lg blue hidden-print uppercase" onclick="pay()"> {{trans('home.service_pay_button')}} </a>
                  	@endif
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/js/layer/layer.js" type="text/javascript"></script>
    <script type="text/javascript">
        // 校验优惠券是否可用
        function redeemCoupon() {
            var coupon_sn = $('#coupon_sn').val();
            var goods_price = '{{$goods->price}}';

            $.ajax({
                type: "POST",
                url: "{{url('redeemCoupon')}}",
                async: false,
                data: {_token:'{{csrf_token()}}', coupon_sn:coupon_sn},
                dataType: 'json',
                beforeSend: function () {
                    index = layer.load(1, {
                        shade: [0.7,'#CCC']
                    });
                },
                success: function (ret) {
                    console.log(ret);
                    layer.close(index);
                    $("#coupon_sn").parent().removeClass("has-error");
                    $("#coupon_sn").parent().removeClass("has-success");
                    $(".input-group-addon").remove();
                    if (ret.status == 'success') {
                        $("#coupon_sn").parent().addClass('has-success');
                        $("#coupon_sn").parent().prepend('<span class="input-group-addon"><i class="fa fa-check fa-fw"></i></span>');

                        // 根据类型计算折扣后的总金额
                        var total_price = 0;
                        if (ret.data.type == '2') {
                            total_price = goods_price * ret.data.discount / 10;
                        } else {
                            total_price = goods_price - ret.data.amount;
                            total_price = total_price > 0 ? total_price : 0;
                        }

                        // 四舍五入，保留2位小数
                        total_price = total_price.toFixed(2);

                        $(".grand-total").text("￥" + total_price);
                    } else {
                        $(".grand-total").text("￥" + goods_price);
                        $("#coupon_sn").parent().addClass('has-error');
                        $("#coupon_sn").parent().remove('.input-group-addon');
                        $("#coupon_sn").parent().prepend('<span class="input-group-addon"><i class="fa fa-remove fa-fw"></i></span>');

                        layer.msg(ret.message);
                    }
                }
            });
        }

        // 在线支付
        function onlinePay(pay_type) {
            var goods_id = '{{$goods->id}}';
            var coupon_sn = $('#coupon_sn').val();

            index = layer.load(1, {
                shade: [0.7,'#CCC']
            });

            $.ajax({
                type: "POST",
                url: "{{url('payment/create')}}",
                async: false,
                data: {_token:'{{csrf_token()}}', goods_id:goods_id, coupon_sn:coupon_sn, pay_type:pay_type},
                dataType: 'json',
                beforeSend: function () {
                    index = layer.load(1, {
                        shade: [0.7,'#CCC']
                    });
                },
                success: function (ret) {
                    layer.msg(ret.message, {time:1300}, function() {
                        if (ret.status == 'success') {
                            if (pay_type==4) {
                                // 如果是Alipay支付写入Alipay的支付页面
                                document.body.innerHTML += ret.data;
                                document.forms['alipaysubmit'].submit();
                            } else {
                                window.location.href = '{{url('payment')}}' + "/" + ret.data;
                            }
                        } else {
                            window.location.href = '{{url('invoices')}}';
                        }
                    });
                }
                //complete: function () {
                    //
                //}
            });
        }

        // 余额支付
        function pay() {
            var goods_id = '{{$goods->id}}';
            var coupon_sn = $('#coupon_sn').val();

            index = layer.load(1, {
                shade: [0.7,'#CCC']
            });

            $.ajax({
                type: "POST",
                url: "/buy/" + goods_id,
                async: false,
                data: {_token:'{{csrf_token()}}', coupon_sn:coupon_sn},
                dataType: 'json',
                beforeSend: function () {
                    index = layer.load(1, {
                        shade: [0.7,'#CCC']
                    });
                },
                success: function (ret) {
                    layer.msg(ret.message, {time:1300}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('invoices')}}';
                        } else {
                            layer.close(index);
                        }
                    });
                }
            });
        }
    </script>
@endsection