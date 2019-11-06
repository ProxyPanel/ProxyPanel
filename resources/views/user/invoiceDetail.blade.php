@extends('user.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.css">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-3">
                        <h3>
                            @if(\App\Components\Helpers::systemConfig()['website_logo'])
                                <img class="navbar-brand-logo mr-10" src="{{\App\Components\Helpers::systemConfig()['website_logo']}}">
                            @else
                                <img class="navbar-brand-logo" src="/assets/images/logo64.png" alt="Otaku Logo">
                            @endif
                            {{\App\Components\Helpers::systemConfig()['website_name']}}
                        </h3>
                    </div>
                    <div class="col-lg-3 offset-lg-6 text-right">
                        <h4>{{trans('home.invoice_title')}}</h4>
                        <p>{{trans('home.invoice_table_id')}}：<a class="font-size-20" href="javascript:void(0)">{{$order->order_sn}}</a>
                        </p>
                        <p>{{trans('home.invoice_table_pay_way')}}：{{$order->pay_way === 1 ? trans('home.service_pay_button') : trans('home.online_pay')}}</p>
                        <p>{{trans('home.invoice_table_create_date')}}: {{$order->created_at}}</p>
                        <p>{{trans('home.invoice_table_expire_at')}}: {{$order->expire_at}}</p>
                        <p>
                            @if(!$order->is_expire)
                                @if($order->status == -1)
                                    <button class="btn btn-dark">{{trans('home.invoice_table_closed')}}</button>
                                    @elseif($order->status == 0)</button>
                                    <button class="btn btn-default">{{trans('home.invoice_table_wait_payment')}}</button>
                                @elseif($order->status == 1)
                                    <button class="btn btn-info">{{trans('home.invoice_table_wait_confirm')}}</button>
                                @elseif($order->status == 2)
                                    <button class="btn btn-success">{{trans('home.invoice_table_wait_active')}}</button>
                                @else
                                    <button class="btn btn-danger">{{trans('home.invoice_table_expired')}}</button>
                                @endif
                            @else
                                <button class="btn btn-danger">{{trans('home.invoice_table_expired')}}</button>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="page-invoice-table table-responsive">
                    <table class="table table-hover text-center">
                        <thead>
                        <tr>
                            <th>{{trans('home.service_name')}}</th>
                            <th>{{trans('home.service_desc')}} </th>
                            <th>{{trans('home.service_price')}}</th>
                            <th>{{trans('home.service_quantity')}}</th>
                            <th>{{trans('home.coupon')}}</th>
                            <th>{{trans('home.service_total_price')}}</th>
                            <th>状态</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="text-center">
                            <td>
                                <h3>{{$order->goods->name}}</h3>
                            </td>
                            <td>
                                {{trans('home.service_days')}} {{$order->goods->days}} {{trans('home.day')}}
                                <br/>
                                @if($order->goods->type == '2')
                                    {{$order->goods->traffic_label}} {{trans('home.account_bandwidth_usage')}}/{{trans('home.month')}}
                                @else
                                    {{$order->goods->traffic_label}}{{trans('home.account_bandwidth_usage')}}/{{$order->goods->days}} {{trans('home.day')}}
                                @endif
                            </td>
                            <td><strong>¥</strong> {{$order->goods->price}} </td>
                            <td> 1</td>
                            <td>{{$order->coupon ? $order->coupon->name : '无'}}</td>
                            <td> ￥{{$order->goods->price}} </td>
                            <td> {{$order->status_label}} </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-animate btn-animate-side btn-default btn-outline" onclick="window.print();">
                        <span><i class="icon wb-print" aria-hidden="true"></i> Print | 打印</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection