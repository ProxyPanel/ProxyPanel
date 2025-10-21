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
        @if (config('broadcasting.default') !== 'null')
            let pollingStarted = false
            let pollingInterval = null

            function clearAll() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null
                }
            }

            function disconnectEcho() {
                try {
                    if (typeof Echo !== 'undefined') {
                        Echo.leave(`payment-status.{{ $payment->trade_no }}`)
                        Echo.connector?.disconnect?.()
                    }
                } catch (e) {
                    console.error('关闭 Echo 失败:', e)
                }
            }

            function onFinal(status, message) {
                clearAll()
                disconnectEcho()
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
                disconnectEcho()

                pollingInterval = setInterval(() => {
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
                if (typeof Echo === 'undefined' || typeof Pusher === 'undefined') {
                    startPolling()
                    return
                }
                try {
                    const conn = Echo.connector?.pusher?.connection || Echo.connector?.socket
                    if (conn) {
                        conn.bind?.('state_change', s => {
                            if (['disconnected', 'failed', 'unavailable'].includes(s.current)) startPolling()
                        })
                        conn.on?.('disconnect', () => startPolling())
                        conn.on?.('error', () => startPolling())
                    }

                    Echo.channel('payment-status.{{ $payment->trade_no }}')
                        .listen('.payment.status.updated', (e) => {
                            if (['success', 'error'].includes(e.status)) {
                                onFinal(e.status, e.message)
                            }
                        })

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
