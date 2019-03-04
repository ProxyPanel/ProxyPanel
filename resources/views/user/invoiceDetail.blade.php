@extends('user.layouts')
@section('css')
    <link href="/assets/pages/css/invoice-2.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="invoice-content-2 bordered">
            <div class="row invoice-cust-add">
                <div class="col-xs-3">
                    <h2 class="invoice-title">{{trans('home.invoice_table_id')}}</h2>
                    <p class="invoice-desc">{{$order->order_sn}}</p>
                </div>
                <div class="col-xs-3">
                    <h2 class="invoice-title">{{trans('home.invoice_table_pay_way')}}</h2>
                    <p class="invoice-desc">{{$order->pay_way === 1 ? trans('home.service_pay_button') : trans('home.online_pay')}}</p>
                </div>
                <div class="col-xs-3">
                    <h2 class="invoice-title">{{trans('home.invoice_table_create_date')}}</h2>
                    <p class="invoice-desc">{{$order->created_at}}</p>
                </div>
                <div class="col-xs-3">
                    <h2 class="invoice-title">{{trans('home.invoice_table_expire_at')}}</h2>
                    <p class="invoice-desc">{{$order->expire_at}}</p>
                </div>
                <!--
                <div class="col-xs-3">
                    <h2 class="invoice-title uppercase">{{trans('home.invoice_table_status')}}</h2>
                    <p class="invoice-desc">
                        @if(!$order->is_expire)
                            @if($order->status == -1)
                                {{trans('home.invoice_table_closed')}}
                            @elseif($order->status == 0)
                                {{trans('home.invoice_table_wait_payment')}}
                            @elseif($order->status == 1)
                                {{trans('home.invoice_table_wait_confirm')}}
                            @elseif($order->status == 2)
                                {{trans('home.invoice_table_wait_active')}}
                            @else
                                {{trans('home.invoice_table_expired')}}
                            @endif
                        @else
                            {{trans('home.invoice_table_expired')}}
                        @endif
                    </p>
                </div>
                -->
            </div>
            <div class="row invoice-body">
                <div class="col-xs-12 table-responsive">
                    <table class="table table-hover">
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
                                    <h2>{{$order->goods->name}}</h2>
                                    {{trans('home.service_traffic')}} {{$order->goods->traffic_label}}
                                    <br/>
                                    {{trans('home.service_days')}} {{$order->goods->days}} {{trans('home.day')}}
                                </td>
                                <td class="text-center"> ￥{{$order->goods->price}} </td>
                                <td class="text-center"> x 1 </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row invoice-subtotal">
                <div class="col-xs-3">
                    <h2 class="invoice-title"> {{trans('home.service_subtotal_price')}} </h2>
                    <p class="invoice-desc"> ￥{{$order->goods->price}} </p>
                </div>
                <div class="col-xs-3">
                    <h2 class="invoice-title">{{trans('home.coupon')}}</h2>
                    <p class="invoice-desc">{{$order->coupon ? $order->coupon->name : '未使用'}}</p>
                </div>
                <div class="col-xs-3">
                    <h2 class="invoice-title"> {{trans('home.service_total_price')}} </h2>
                    <p class="invoice-desc grand-total"> ￥{{$order->amount}} </p>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <a class="btn btn-lg green-haze hidden-print uppercase print-btn" onclick="javascript:window.print();">打印</a>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
@endsection