@extends('user.layouts')

@section('css')
    <link href="/assets/pages/css/invoice-2.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('user/goodsList')}}">流量包</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('user/addOrder')}}">购买</a>
                <i class="fa fa-circle"></i>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="invoice-content-2 bordered">
            <div class="row invoice-body">
                <div class="col-xs-12 table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th class="invoice-title uppercase"> 商品信息 </th>
                            <th class="invoice-title uppercase text-center"> 单价 </th>
                            <th class="invoice-title uppercase text-center"> 数量 </th>
                            <th class="invoice-title uppercase text-center"> 小计 </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td style="padding: 20px;">
                                <h3>{{$goods->name}}</h3>
                                <p> <img src="{{$goods->logo}}" style="width:100px; height:100px;"> 内含流量 {{$goods->traffic}} MiB，有效期 {{date('Y-m-d', strtotime($goods->start_time))}} ~ {{date('Y-m-d', strtotime($goods->end_time))}} </p>
                            </td>
                            <td class="text-center sbold"> ￥{{$goods->price}} </td>
                            <td class="text-center sbold"> x 1 </td>
                            <td class="text-center sbold"> ￥{{$goods->price}} </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row invoice-subtotal">
                <div class="col-xs-3">
                    <h2 class="invoice-title uppercase"> 共计 </h2>
                    <p class="invoice-desc"> ￥{{$goods->price}} </p>
                </div>
                <div class="col-xs-3">
                    <h2 class="invoice-title uppercase"> 优惠 </h2>
                    <p class="invoice-desc">
                        <div class="input-group">
                            <input class="form-control" type="text" name="coupon_sn" id="coupon_sn" placeholder="优惠券" />
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" onclick="redeemCoupon()"><i class="fa fa-refresh"></i> 使用 </button>
                            </span>
                        </div>
                    </p>
                </div>
                <div class="col-xs-6">
                    <h2 class="invoice-title uppercase"> 实际结算 </h2>
                    <p class="invoice-desc grand-total"> ￥{{$goods->price}} </p>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <a class="btn btn-lg green-haze hidden-print uppercase print-btn" onclick="addOrder()"> 支付 </a>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 校验优惠券是否可用
        function redeemCoupon() {
            var coupon_sn = $('#coupon_sn').val();
            var goods_price = '{{$goods->price}}';

            $.ajax({
                type: "POST",
                url: "{{url('user/redeemCoupon')}}",
                async: false,
                data: {_token:'{{csrf_token()}}', coupon_sn:coupon_sn},
                dataType: 'json',
                success: function (ret) {
                    console.log(ret);
                    $("#coupon_sn").parent().removeClass("has-error");
                    $("#coupon_sn").parent().removeClass("has-success");
                    $(".input-group-addon").remove();
                    if (ret.status == 'success') {
                        $("#coupon_sn").parent().addClass('has-success');
                        $("#coupon_sn").parent().prepend('<span class="input-group-addon"><i class="fa fa-check fa-fw"></i></span>');

                        // 根据类型计算折扣后的总金额
                        var total_price = 0;
                        if (ret.data.type == '2') {
                            total_price = goods_price * ret.data.discount;
                        } else {
                            total_price = goods_price - ret.data.amount;
                        }

                        $(".grand-total").text("￥" + total_price);
                    } else {
                        $("#coupon_sn").parent().addClass('has-error');
                        $("#coupon_sn").parent().remove('.input-group-addon');
                        $("#coupon_sn").parent().prepend('<span class="input-group-addon"><i class="fa fa-remove fa-fw"></i></span>');
                    }
                }
            });
        }

        // 添加订单
        function addOrder() {
            var goods_id = '{{$goods->id}}';
            var coupon_sn = $('#coupon_sn').val();

            $.ajax({
                type: "POST",
                url: "{{url('user/addOrder')}}",
                async: false,
                data: {_token:'{{csrf_token()}}', goods_id:goods_id, coupon_sn:coupon_sn},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status == 'success') {
                        bootbox.alert(ret.message, function () {
                            window.location.href = '{{url('user/orderList')}}';
                        });
                    } else {
                        bootbox.alert(ret.message);
                    }
                }
            });
        }
    </script>
@endsection