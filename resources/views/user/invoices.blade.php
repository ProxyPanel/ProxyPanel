@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading p-20">
                <h1 class="panel-title cyan-600"><i class="icon wb-bookmark"></i>{{ trans('user.menu.invoices') }}</h1>
                @if ($prepaidPlan)
                    <div class="panel-actions">
                        <button class="btn btn-primary" onclick="closePlan()">
                            {{ trans('common.active_item', ['attribute' => trans('common.order.status.prepaid')]) }}</button>
                    </div>
                @endif
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                        <tr>
                            <th> #</th>
                            <th> {{ trans('model.order.id') }} </th>
                            <th> {{ trans('user.shop.service') }} </th>
                            <th> {{ trans('user.payment.method') }} </th>
                            <th> {{ trans('user.invoice.amount') }} </th>
                            <th> {{ trans('user.bought_at') }} </th>
                            <th> {{ trans('common.expired_at') }} </th>
                            <th> {{ trans('common.status.attribute') }} </th>
                            <th> {{ trans('common.action') }} </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderList as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('invoice.show', $order->sn) }}" target="_blank">{{ $order->sn }}</a></td>
                                <td>{{ $order->goods->name ?? trans('user.recharge_credit') }}</td>
                                <td>{{ $order->pay_way === 1 ? trans('user.shop.pay_credit') : trans('user.shop.pay_online') }}</td>
                                <td>{{ $order->amount_tag }}</td>
                                <td>{{ $order->created_at }}</td>
                                <td>{{ empty($order->goods) || $order->goods_id === null || $order->status === 3 ? '' : $order->expired_at }}</td>
                                <td>{!! $order->status_label !!}</td>
                                <td>
                                    <div class="btn-group">
                                        @if ($order->status === 0 && $order->pay_way !== 1)
                                            @if ($order->payment)
                                                @if ($order->payment->qr_code)
                                                    <a class="btn btn-primary" href="{{ route('orderDetail', $order->payment->trade_no) }}"
                                                       target="_blank">{{ trans('user.pay') }}</a>
                                                @elseif($order->payment->url)
                                                    <a class="btn btn-primary" href="{{ $order->payment->url }}" target="_blank">{{ trans('user.pay') }}</a>
                                                @endif
                                            @endif
                                            <button class="btn btn-danger" onclick="closeOrder('{{ $order->id }}')">{{ trans('common.cancel') }}</button>
                                        @elseif ($order->status === 1)
                                            <button class="btn btn-primary" onClick="window.location.reload();">
                                                <i class="icon wb-refresh" aria-hidden="true"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-12">
                        <nav class="Page navigation float-right">
                            {{ $orderList->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script>
        function closePlan() {
            showConfirm({
                title: '{{ trans('user.invoice.active_prepaid_question') }}',
                html: `{!! trans('user.invoice.active_prepaid_tips') !!}`,
                icon: 'warning',
                onConfirm: function() {
                    ajaxPost('{{ route('invoice.activate') }}');
                }
            });
        }

        function closeOrder(id) {
            showConfirm({
                title: '{{ trans('common.close_item', ['attribute' => trans('user.invoice.attribute')]) }}？',
                icon: 'warning',
                onConfirm: function() {
                    ajaxPut(jsRoute('{{ route('closeOrder', 'PLACEHOLDER') }}', id));
                }
            });
        }
    </script>
@endsection
