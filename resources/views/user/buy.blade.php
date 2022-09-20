@extends('user.layouts')
@section('css')
    <link href="/assets/css/invoice.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content">
        <div class="panel">
            <div class="panel-body container-fluid">
                <div class="page-invoice-table table-responsive">
                    <table class="table table-hover text-md-center">
                        <thead>
                        <tr>
                            <th>{{trans('user.shop.service')}}</th>
                            <th>{{trans('user.shop.description')}} </th>
                            <th>{{trans('user.shop.price')}}</th>
                            <th>{{trans('user.shop.quantity')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="text-middle">{{$goods->name}} </td>
                            <td>{{trans('common.available_date')}}
                                <strong>{{$goods->type === 1? $dataPlusDays : $goods->days}} {{trans_choice('validation.attributes.day', 1)}}</strong>
                                <br>
                                <strong>{{$goods->traffic_label}}</strong> {{trans('user.attribute.data')}}
                                <br>
                                {{trans('user.account.speed_limit')}}<strong> {{ $goods->speed_limit ? $goods->speed_limit.' Mbps' : trans('user.service.unlimited') }} </strong>
                            </td>
                            <td class="text-middle"> ¥{{$goods->price}} </td>
                            <td class="text-middle"> x 1</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="row">
                    @if($goods->type <= 2)
                        <div class="col-lg-3 pl-30">
                            <div class="input-group">
                                <input type="text" class="form-control" name="coupon_sn" id="coupon_sn" placeholder="{{trans('user.coupon.attribute')}}"/>
                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-info" onclick="redeemCoupon()">
                                        <i class="icon wb-loop" aria-hidden="true"></i> {{trans('common.apply')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 offset-lg-6 text-right">
                            <p id="discount"></p>
                            <p>{{trans('user.shop.subtotal')}}
                                <span>¥{{$goods->price}}</span>
                            </p>
                            <p class="page-invoice-amount">{{trans('user.shop.total')}}
                                <span class="grand-total">¥{{$goods->price}}</span>
                            </p>
                        </div>
                    @endif
                    <div class="col-md-12 mb-30">
                        <div class="float-right">
                            @include('user.components.purchase')
                            @if($goods->type <= 2)
                                <button class="btn btn-flat mt-2 mx-0 p-0" onclick="pay('credit','0')">
                                    <img src="/assets/images/payment/creditpay.svg" height="48px" alt="{{trans('user.shop.pay_credit')}}"/>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
        // 校验优惠券是否可用
        function redeemCoupon() {
            const coupon_sn = $('#coupon_sn').val();
            const goods_price = '{{$goods->price}}';
            $.ajax({
                method: 'POST',
                url: '{{route('redeemCoupon', $goods)}}',
                dataType: 'json',
                data: {_token: '{{csrf_token()}}', coupon_sn: coupon_sn},
                success: function(ret) {
                    $('.input-group-prepend').remove();
                    if (ret.status === 'success') {
                        $('#coupon_sn').parent().prepend(
                            '<div class="input-group-prepend"><span class="input-group-text bg-green-700"><i class="icon wb-check white" aria-hidden="true"></i></span></div>');
                        // 根据类型计算折扣后的总金额
                        let total_price = 0;
                        let coupon_text = document.getElementById('discount');
                        if (ret.data.type === 2) {
                            total_price = goods_price * (1 - ret.data.value / 100);
                            coupon_text.innerHTML = '【{{trans('user.coupon.attribute')}}】：' + ret.data.name + ' － '
                                + (100 - ret.data.value) + '%<br> {{trans('user.coupon.discount')}} － ¥' + total_price.toFixed(2);
                            total_price = goods_price - total_price;
                        } else {
                            total_price = goods_price - ret.data.value;
                            total_price = total_price > 0 ? total_price : 0;
                            if (ret.data.type === 1) {
                                coupon_text.innerHTML = '【{{trans('user.coupon.voucher')}}】：' + ret.data.name + ' －¥' + ret.data.value;
                            }
                        }

                        // 四舍五入，保留2位小数
                        $('.grand-total').text('¥' + total_price.toFixed(2));
                        swal.fire({
                            title: ret.message,
                            icon: 'success',
                            timer: 1300,
                            showConfirmButton: false,
                        });
                    } else {
                        $('.grand-total').text('¥' + goods_price);
                        $('#coupon_sn').parent().prepend(
                            '<div class="input-group-prepend"><span class="input-group-text bg-red-700"><i class="icon wb-close white" aria-hidden="true"></i></span></div>');
                        swal.fire({
                            title: ret.title,
                            text: ret.message,
                            icon: 'error',
                        });
                    }
                },
            });
        }

        // 检查预支付
        function pay(method, pay_type) {
            // 存在套餐 和 购买类型为套餐时 出现提示
            if ('{{$activePlan}}' === '1' && '{{$goods->type}}' === '2') {
                swal.fire({
                    title: '{{trans('user.shop.conflict')}}',
                    html: '{!! trans('user.shop.conflict_tips') !!}',
                    icon: 'info',
                    showCancelButton: true,
                    cancelButtonText: '{{trans('common.back')}}',
                    confirmButtonText: '{{trans('common.continues')}}',
                }).then((result) => {
                    if (result.value) {
                        contiousPay(method, pay_type);
                    }
                });
            } else {
                contiousPay(method, pay_type);
            }
        }

        function contiousPay(method, pay_type) {
            const goods_id = '{{$goods->id}}';
            const coupon_sn = $('#coupon_sn').val();
            $.ajax({
                method: 'POST',
                url: '{{route('purchase')}}',
                dataType: 'json',
                data: {
                    _token: '{{csrf_token()}}',
                    goods_id: goods_id,
                    coupon_sn: coupon_sn,
                    method: method,
                    pay_type: pay_type,
                },
                success: function(ret) {
                    if (ret.status === 'success') {
                        swal.fire({
                            title: ret.message,
                            icon: 'success',
                            timer: 1300,
                            showConfirmButton: false,
                        });
                        if (method === 'credit') {
                            swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.href = '{{route('invoice')}}');
                        }
                        if (ret.data) {
                            window.location.href = '{{route('orderDetail', '')}}/' + ret.data;
                        } else if (ret.url) {
                            window.location.href = ret.url;
                        }
                    } else if (ret.status === 'info') {
                        swal.fire({title: ret.title, text: ret.message, icon: 'question'});
                    } else {
                        swal.fire({
                            title: ret.message,
                            icon: 'error',
                        });
                    }
                },
                error: function() {
                    swal.fire('{{trans('user.unknown').trans('common.error')}}', '{{trans('user.shop.call4help')}}', 'error');
                },
            });
        }
    </script>
@endsection
