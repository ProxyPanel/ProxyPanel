@extends('_layout')
@section('title', sysConfig('website_name'))
@section('body_class','page-login-v3 layout-full')
@section('layout_css')
    <style>
        .layout-full {
            margin-right: auto !important;
            margin-left: auto !important;
        }

        @media (min-width: 992px) {
            .layout-full {
                max-width: 75vw;
            }
        }

        @media (min-width: 1200px) {
            .layout-full {
                max-width: 50vw;
            }
        }
    </style>
@endsection
@section('layout_content')
    <div class="page vertical-align " data-animsition-in="fade-in" data-animsition-out="fade-out">
        <div class="page-content container vertical-align-middle">
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-line @if( $order->status === 2 ) panel-success @else panel-danger @endif">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <i class="icon wb-shopping-cart" aria-hidden="true"></i> {{trans('user.invoice.detail')}}
                            </h3>
                        </div>
                        <div class="panel-body">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    订单原价：{{ $order->origin_amount_tag }}
                                </li>
                                <li class="list-group-item">
                                    实际支付金额：{{ $order->origin_amount_tag }}
                                </li>
                                <li class="list-group-item">
                                    {{ trans('user.payment_method') }}：
                                    {{ $order->pay_way === 1 ? trans('user.shop.pay_credit') : trans('user.shop.pay_online') }}
                                </li>
                                <li class="list-group-item">
                                    {{ trans('user.bought_at') }}: {{ $order->created_at }}
                                </li>
                                @if($order->expired_at)
                                    <li class="list-group-item">
                                        {{ trans('common.expired_at') }}: {{ $order->expired_at }}
                                    </li>
                                @endif
                                <li class="list-group-item">
                                    {{ trans('common.status') }}：{!! $order->status_label !!}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-line panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <i class="icon wb-user-circle" aria-hidden="true"></i> 用户信息
                            </h3>
                        </div>
                        <div class="panel-body">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    {{ trans('validation.attributes.nickname') }}：{{ $user->nickname }}
                                </li>
                                <li class="list-group-item">
                                    {{ trans('validation.attributes.username') }}：{{ $user->username }}
                                </li>
                                <li class="list-group-item">
                                    {{ trans('user.attribute.data') }}：{{ flowAutoShow($user->used_traffic) }} / {{ flowAutoShow($user->transfer_enable) }}
                                </li>
                                <li class="list-group-item">
                                    余额：{{ $user->credit }}
                                </li>
                                <li class="list-group-item">
                                    过期时间：{{ $user->expiration_date }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
