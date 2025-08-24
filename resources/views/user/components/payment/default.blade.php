@extends('user.layouts')
@section('content')
    <div class="page-content container">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title cyan-600">
                    <i class="icon wb-payment" aria-hidden="true"></i>{{ sysConfig('website_name') . ' - ' . trans('user.shop.pay_online') }}
                </h1>
            </div>
            <div class="panel-body border-primary ml-auto mr-auto w-p75">
                <div class="alert alert-info text-center">
                    {!! trans('user.payment.qrcode_tips', ['software' => $pay_type]) !!}
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-dividered">
                            <li class="list-group-item">{{ trans('user.shop.service') . ': ' . $name }}</li>
                            <li class="list-group-item">{{ trans('user.shop.price') . ': ' . $payment->amount_tag }}</li>
                            @if ($days !== 0)
                                <li class="list-group-item">{{ trans('common.available_date') . ': ' . $days . trans_choice('common.days.attribute', 1) }}</li>
                            @endif
                            <li class="list-group-item"> {!! trans('user.payment.close_tips', ['minutes' => (time() - strtotime(sysConfig('tasks_close.orders'))) / 60]) !!}</li>
                        </ul>
                    </div>
                    <div class="col-auto mx-auto">
                        @if ($payment->qr_code && $payment->url)
                            <div class=" w-p100 h-p100" id="qrcode"></div>
                        @else
                            <img class="h-250 w-250" src="{{ $payment->qr_code }}" alt="{{ trans('common.qrcode', ['attribute' => trans('user.pay')]) }}">
                        @endif
                    </div>
                </div>
                <div class="alert alert-danger text-center mt-10">
                    {!! trans('user.payment.mobile_tips') !!}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    @if ($payment->qr_code && $payment->url)
        <script src="/assets/custom/easy.qrcode.min.js"></script>
        <script>
            // Options
            const options = {
                text: @json($payment->url),
                backgroundImage: '{{ asset($pay_type_icon) }}',
                autoColor: true
            };

            // Create QRCode Object
            new QRCode(document.getElementById("qrcode"), options);
        </script>
    @endif

    <script>
        // 检查支付单状态
        let pollingInterval = window.setInterval(function() {
            ajaxGet('{{ route('orderStatus') }}', {
                trade_no: '{{ $payment->trade_no }}'
            }, {
                success: function(ret) {
                    if (ret.status === "success" || ret.status === "error") {
                        window.clearInterval(pollingInterval);

                        if (ret.status === "success") {
                            showMessage({
                                title: ret.message,
                                icon: "success",
                                showConfirmButton: false,
                                callback: function() {
                                    window.location.href = '{{ route('invoice.index') }}';
                                }
                            });
                        } else if (ret.status === "error") {
                            showMessage({
                                title: ret.message,
                                icon: "error",
                                showConfirmButton: false,
                                callback: function() {
                                    window.location.href = '{{ route('invoice.index') }}';
                                }
                            });
                        }
                    }
                }
            });
        }, 3000);

        window.addEventListener('beforeunload', function() {
            if (pollingInterval) {
                window.clearInterval(pollingInterval);
            }
        });
    </script>
@endsection
