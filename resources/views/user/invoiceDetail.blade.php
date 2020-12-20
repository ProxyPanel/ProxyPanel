@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-3">
                        <h3>
                            <img src="{{sysConfig('website_logo')? asset(sysConfig('website_logo')) :'/assets/images/logo64.png'}}" class="navbar-brand-logo" alt="logo">
                            {{sysConfig('website_name')}}
                        </h3>
                    </div>
                    <div class="col-lg-3 offset-lg-6 text-right">
                        <h4>{{trans('home.invoice_title')}}</h4>
                        <p>{{trans('home.invoice_table_id')}}：
                            <a class="font-size-20" href="javascript:void(0)">{{$order->order_sn}}</a>
                        </p>
                        <p>{{trans('home.invoice_table_pay_way')}}
                            ：{{$order->pay_way === 1 ? trans('home.service_pay_button') : trans('home.online_pay')}}</p>
                        <p>{{trans('home.invoice_table_create_date')}}: {{$order->created_at}}</p>
                        <p>{{trans('home.invoice_table_expired_at')}}: {{$order->expired_at}}</p>
                        <p>
                        @if(!$order->is_expire)
                            @if($order->status === -1)
                                <p class="badge badge-lg badge-dark">{{trans('home.invoice_table_closed')}}</p>
                            @elseif($order->status === 0)
                                <p class="badge badge-lg badge-default">{{trans('home.invoice_table_wait_payment')}}</p>
                            @elseif($order->status === 1)
                                <p class="badge badge-lg badge-info">{{trans('home.invoice_table_wait_confirm')}}</p>
                            @elseif($order->status === 2)
                                <p class="badge badge-lg badge-success">{{trans('home.invoice_table_wait_active')}}</p>
                            @else
                                <p class="badge badge-lg badge-danger">{{trans('home.invoice_table_expired')}}</p>
                            @endif
                        @else
                            <p class="badge badge-lg badge-danger">{{trans('home.invoice_table_expired')}}</p>
                        @endif
                    </div>
                </div>
                <div class="page-invoice-table table-responsive">
                    <table class="table table-hover text-md-center">
                        <thead class="thead-info">
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
                        <tr>
                            <td>
                                <h3>{{$order->goods->name ?? ($order->goods_id === 0 ? '余额充值': trans('home.invoice_table_goods_deleted'))}}</h3>
                            </td>
                            <td>
                                @if($order->goods)
                                    {{trans('home.service_days')}}
                                    <code>{{$order->goods->days}}</code> {{trans('home.day')}}
                                    <br/>
                                    @if($order->goods->type === 2)
                                        <code>{{$order->goods->traffic_label}}</code>
                                        {{trans('home.bandwidth')}}/{{trans('home.month')}}
                                    @else
                                        <code>{{$order->goods->traffic_label}}</code>
                                        {{trans('home.bandwidth')}}/
                                        <code>{{$order->goods->days}}</code>
                                        {{trans('home.day')}}
                                    @endif
                                @else
                                    余额充值
                                @endif
                            </td>
                            <td><strong>¥</strong> {{$order->origin_amount}} </td>
                            <td> 1</td>
                            <td>{{$order->coupon->name ?? '无'}}</td>
                            <td> ￥{{$order->amount}} </td>
                            <td> {{$order->status_label}} </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-animate btn-animate-side btn-outline-info" onclick="window.print();">
                        <span><i class="icon wb-print" aria-hidden="true"></i> Print | 打印</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
@endsection
