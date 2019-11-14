@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading p-20">
                <h1 class="panel-title cyan-600"><i class="icon wb-plus"></i>{{trans('home.invoices')}}</h1>
            </div>
            <div class="panel-body">
                <table class="table text-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{trans('home.invoice_table_id')}} </th>
                        <th> {{trans('home.invoice_table_name')}} </th>
                        <th> {{trans('home.invoice_table_pay_way')}} </th>
                        <th> {{trans('home.invoice_table_price')}} </th>
                        <th> {{trans('home.invoice_table_create_date')}} </th>
                        <th> {{trans('home.invoice_table_expire_at')}} </th>
                        <th> {{trans('home.invoice_table_status')}} </th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($orderList->isEmpty())
                        <tr>
                            <td colspan="8">{{trans('home.invoice_table_none')}}</td>
                        </tr>
                    @else
                        @foreach($orderList as $order)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td><a href="/invoice/{{$order->order_sn}}" target="_blank">{{$order->order_sn}}</a>
                                </td>
                                <td>{{empty($order->goods) ? trans('home.invoice_table_goods_deleted') : $order->goods->name}}</td>
                                <td>{{$order->pay_way === 1 ? trans('home.service_pay_button') : trans('home.online_pay')}}</td>
                                <td>￥{{$order->amount}}</td>
                                <td>{{$order->created_at}}</td>
                                <td>{{empty($order->goods) ? '' : ($order->goods->type == 3 ? '' : $order->expire_at)}}</td>
                                <td>
                                    @if(!$order->is_expire)
                                        @if($order->status == -1)
                                            <a href="javascript:" class="btn btn-sm btn-default disabled"> {{trans('home.invoice_table_closed')}} </a>
                                        @elseif($order->status == 0)
                                            <a href="javascript:" class="btn btn-sm btn-dark disabled"> {{trans('home.invoice_table_wait_payment')}} </a>
                                            @if(!empty($order->payment))
                                                @if(!empty($order->payment->jump_url))
                                                    <a href="{{$order->payment->jump_url}}" target="_blank" class="btn btn-sm btn-danger">{{trans('home.pay')}}</a>
                                                @else
                                                    <a href="/payment/{{$order->payment->sn}}" target="_blank" class="btn btn-sm btn-danger">{{trans('home.pay')}}</a>
                                                @endif
                                            @endif
                                        @elseif($order->status == 1)
                                            <a href="javascript:" class="btn btn-sm btn-dark disabled"> {{trans('home.invoice_table_wait_confirm')}} </a>
                                        @elseif($order->status == 2)
                                            @if(!empty($order->goods) && $order->goods->type == 3)
                                                <a href="javascript:" class="btn btn-sm btn-success disabled"> 支付成功 </a>
                                            @else
                                                <a href="javascript:" class="btn btn-sm btn-success disabled"> {{trans('home.invoice_table_wait_active')}} </a>
                                            @endif
                                        @else
                                            <a href="javascript:" class="btn btn-sm btn-default disabled"> {{trans('home.invoice_table_expired')}} </a>
                                        @endif
                                    @else
                                        <a href="javascript:" class="btn btn-sm btn-default disabled"> {{trans('home.invoice_table_expired')}} </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                <div class="panel-footer">
                    <nav class="Page navigation float-right">
                        {{$orderList->links()}}
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection
