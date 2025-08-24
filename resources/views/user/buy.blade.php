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
                                <th>{{ trans('user.shop.service') }}</th>
                                <th>{{ trans('user.shop.description') }} </th>
                                <th>{{ trans('user.shop.price') }}</th>
                                <th>{{ trans('user.shop.quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-middle">{{ $goods->name }} </td>
                                <td>{{ trans('common.available_date') }}
                                    <strong>{{ $goods->type === 1 ? $dataPlusDays : $goods->days }} {{ trans_choice('common.days.attribute', 1) }}</strong>
                                    <br>
                                    <strong>{{ $goods->traffic_label }}</strong> {{ trans('user.attribute.data') }}
                                    <br>
                                    {{ trans('user.account.speed_limit') }}
                                    <strong> {{ $goods->speed_limit ? $goods->speed_limit . ' Mbps' : trans('user.service.unlimited') }} </strong>
                                </td>
                                <td class="text-middle"> {{ $goods->price_tag }} </td>
                                <td class="text-middle"> x 1</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="row">
                    @if ($goods->type <= 2)
                        <div class="col-lg-3 pl-30">
                            <div class="input-group">
                                <input class="form-control" id="coupon_sn" name="coupon_sn" type="text" placeholder="{{ trans('model.coupon.attribute') }}" />
                                <div class="input-group-btn">
                                    <button class="btn btn-info" type="submit" onclick="redeemCoupon()">
                                        <i class="icon wb-loop" aria-hidden="true"></i> {{ trans('common.apply') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 offset-lg-6 text-right">
                            <p id="discount"></p>
                            <p>{{ trans('user.shop.subtotal') }}
                                <span>{{ $goods->price_tag }}</span>
                            </p>
                            <p class="page-invoice-amount">{{ trans('user.shop.total') }}
                                <span class="grand-total">{{ $goods->price_tag }}</span>
                            </p>
                        </div>
                    @endif
                    <div class="col-md-12 mb-30">
                        <div class="float-right">
                            @include('user.components.purchase')
                            @if ($goods->type <= 2)
                                <button class="btn btn-flat mt-2 mx-0 p-0" onclick="pay('credit', '0')">
                                    <img src="/assets/images/payment/creditpay.svg" alt="{{ trans('user.shop.pay_credit') }}" height="48px" />
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
        function redeemCoupon() { // 校验优惠券是否可用
            const coupon_sn = $('#coupon_sn').val();
            let tag = '{{ $goods->price_tag }}'.match(/(.*?[^0-9])(\d+\.?.*)/);
            const goods_price = tag[2];
            const sign = tag[1];
            ajaxPost('{{ route('shop.coupon.check', $goods) }}', {
                coupon_sn: coupon_sn
            }, {
                success: function(ret) {
                    $('.input-group-prepend').remove();
                    if (ret.status === 'success') {
                        $('#coupon_sn').parent().prepend(
                            '<div class="input-group-prepend"><span class="input-group-text bg-green-700"><i class="icon wb-check white" aria-hidden="true"></i></span></div>'
                        );
                        // 根据类型计算折扣后的总金额
                        let total_price;
                        let coupon_text = document.getElementById('discount');
                        if (ret.data.type === 2) {
                            const discount = goods_price * (1 - ret.data.value / 100);

                            coupon_text.innerHTML = '【{{ trans('admin.coupon.type.discount') }}】: ' + ret.data.name + ' _ ' +
                                (100 - ret.data.value) + '%<br> {{ trans('user.coupon.discount') }}: ➖ ' + sign +
                                discount.toFixed(2);
                            total_price = goods_price - discount;
                        } else {
                            total_price = goods_price - ret.data.value.match(/(.*?[^0-9])(\d+\.?.*)/)[2];
                            total_price = total_price > 0 ? total_price : 0;
                            if (ret.data.type === 1) {
                                coupon_text.innerHTML = '【{{ trans('admin.coupon.type.voucher') }}】: ' + ret.data.name + ' －' +
                                    ret.data.value;
                            }
                        }

                        // 四舍五入，保留2位小数
                        $('.grand-total').text(sign + total_price.toFixed(2));
                    } else {
                        $('.grand-total').text(sign + goods_price);
                        $('#coupon_sn').parent().prepend(
                            '<div class="input-group-prepend"><span class="input-group-text bg-red-700"><i class="icon wb-close white" aria-hidden="true"></i></span></div>'
                        );
                    }
                }
            });
        }

        function pay(method, pay_type) { // 检查预支付
            if ('{{ $activePlan }}' === '1' && '{{ $goods->type }}' === '2') { // 存在套餐 和 购买类型为套餐时 出现提示
                showConfirm({
                    title: '{{ trans('user.shop.conflict') }}',
                    html: '{!! trans('user.shop.conflict_tips') !!}',
                    icon: 'info',
                    cancelButtonText: '{{ trans('common.back') }}',
                    confirmButtonText: '{{ trans('common.continue') }}',
                    onConfirm: function() {
                        continuousPayment(method, pay_type);
                    }
                });
            } else {
                continuousPayment(method, pay_type);
            }
        }

        function continuousPayment(method, pay_type) {
            ajaxPost('{{ route('purchase') }}', {
                goods_id: '{{ $goods->id }}',
                coupon_sn: $('#coupon_sn').val(),
                method: method,
                pay_type: pay_type,
            }, {
                success: function(ret) {
                    let options = {};
                    if (ret.status === 'success') {
                        if (method === 'credit') {
                            options.redirectUrl = '{{ route('invoice.index') }}';
                        } else if (ret.data) {
                            options.redirectUrl = '{{ route('orderDetail', '') }}/' + ret.data;
                        } else if (ret.url) {
                            options.redirectUrl = ret.url;
                        }
                    }
                    handleResponse(ret, options)
                }
            });
        }
    </script>
@endsection
