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
    @vite(['resources/js/app.js'])
    @if ($payment->qr_code && $payment->url)
        <script src="/assets/custom/easy.qrcode.min.js"></script>
        <script>
            // Create QRCode Object
            new QRCode(document.getElementById("qrcode"), {
                text: @json($payment->url),
                backgroundImage: '{{ asset($pay_type_icon) }}',
                autoColor: true
            });
        </script>
    @endif

    <script>
        window.i18n.extend({
            'broadcast': {
                'error': '{{ trans('common.error') }}',
                'websocket_unavailable': '{{ trans('common.broadcast.websocket_unavailable') }}',
                'websocket_disconnected': '{{ trans('common.broadcast.websocket_disconnected') }}',
                'setup_failed': '{{ trans('common.broadcast.setup_failed') }}',
                'disconnect_failed': '{{ trans('common.broadcast.disconnect_failed') }}'
            }
        });
        @if (config('broadcasting.default') !== 'null')
            let pollingStarted = false

            function onFinal(status, message) {
                window.broadcastingManager.stopPolling('payment-status'); // 停止轮询
                window.broadcastingManager.disconnect(); // 断开连接
                showMessage({
                    title: message,
                    icon: status === 'success' ? 'success' : 'error',
                    showConfirmButton: false,
                    callback: () => window.location.href = '{{ route('invoice.index') }}'
                })
            }

            function startPolling() {
                if (pollingStarted) return
                pollingStarted = true
                window.broadcastingManager.disconnect(); // 断开连接

                // 使用统一的广播管理器启动轮询
                window.broadcastingManager.startPolling('payment-status', () => {
                    ajaxGet('{{ route('orderStatus') }}', {
                        trade_no: '{{ $payment->trade_no }}'
                    }, {
                        success: ret => {
                            if (['success', 'error'].includes(ret.status)) {
                                onFinal(ret.status, ret.message)
                            }
                        },
                        error: () => onFinal('error', "{{ trans('common.request_failed') }}")
                    })
                }, 3000)
            }

            function setupPaymentListener() {
                // 使用统一的广播管理器检查 Echo 是否可用
                if (!window.broadcastingManager.isEchoAvailable()) {
                    startPolling()
                    return
                }

                try {
                    // 使用统一的广播管理器订阅频道
                    const success = window.broadcastingManager.subscribe(
                        'payment-status.{{ $payment->trade_no }}',
                        '.payment.status.updated',
                        (e) => {
                            if (['success', 'error'].includes(e.status)) {
                                onFinal(e.status, e.message)
                            }
                        }
                    );

                    if (!success) {
                        startPolling()
                    }

                } catch (e) {
                    console.error('Echo 初始化失败:', e)
                    startPolling()
                }
            }

            window.addEventListener('load', setupPaymentListener)
        @else
            startPolling()
        @endif
    </script>
@endsection
