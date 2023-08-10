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
                        <h4>{{trans('user.invoice.detail')}}</h4>
                        <p>{{ trans('model.order.id') }}
                            :<a class="font-size-20" href="javascript:void(0)">{{$order->sn}}</a>
                        </p>
                        <p>{{trans('user.payment_method')}}
                            : {{$order->pay_way === 1 ? trans('user.shop.pay_credit') : trans('user.shop.pay_online')}}</p>
                        <p>{{trans('user.bought_at')}}: {{$order->created_at}}</p>
                        @if($order->expired_at)
                            <p>{{trans('common.expired_at')}}: {{$order->expired_at}}</p>
                        @endif
                    </div>
                </div>
                <div class="page-invoice-table table-responsive">
                    <table class="table table-hover text-md-center">
                        <thead class="thead-info">
                        <tr>
                            <th>{{trans('user.shop.service')}}</th>
                            <th>{{trans('user.shop.description')}} </th>
                            <th>{{trans('user.shop.price')}}</th>
                            <th>{{trans('user.shop.quantity')}}</th>
                            <th>{{trans('model.coupon.attribute')}}</th>
                            <th>{{trans('user.shop.total')}}</th>
                            <th>{{trans('common.status.attribute')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <h3>{{$order->goods->name ?? trans('user.recharge_credit')}}</h3>
                            </td>
                            <td>
                                @if($order->goods)
                                    {{trans('common.available_date')}}
                                    <code>{{$order->goods->days}}</code> {{trans_choice('common.days.attribute', 1)}}
                                    <br/>
                                    @if($order->goods->type === 2)
                                        <code>{{$order->goods->traffic_label}}</code>
                                        {{trans('user.attribute.data')}}/{{trans('validation.attributes.month')}}
                                    @else
                                        <code>{{$order->goods->traffic_label}}</code>
                                        {{trans('user.attribute.data')}}/
                                        <code>{{$order->goods->days}}</code>
                                        {{trans_choice('common.days.attribute', 1)}}
                                    @endif
                                @else
                                    {{trans('user.recharge_credit')}}
                                @endif
                            </td>
                            <td> {{$order->origin_amount_tag}} </td>
                            <td> 1</td>
                            <td>{{$order->coupon->name ?? trans('common.none')}}</td>
                            <td> {{$order->amount_tag}} </td>
                            <td> {!! $order->status_label !!} </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-animate btn-animate-side btn-outline-info" onclick="window.print();">
                        <span><i class="icon wb-print" aria-hidden="true"></i> {{trans('common.print')}} </span>
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
