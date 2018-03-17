@extends('user.layouts')

@section('css')
    <link href="/assets/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet" type="text/css" />
    <style>
        .fancybox > img {
            width: 75px;
            height: 75px;
        }
    </style>
@endsection
@section('title', trans('home.panel'))
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top: 0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light table-checkable order-column">
                                <thead>
                                    <tr>
                                        <th style="width:20%;"> 编号 </th>
                                        <th> {{trans('home.invoice_table_name')}} </th>
                                        <th> {{trans('home.invoice_table_price')}} </th>
                                        <th> {{trans('home.invoice_table_create_date')}} </th>
                                        <th> {{trans('home.invoice_table_status')}} </th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if($orderList->isEmpty())
                                    <tr>
                                        <td colspan="5">{{trans('home.invoice_table_none')}}</td>
                                    </tr>
                                @else
                                    @foreach($orderList as $key => $order)
                                        <tr class="odd gradeX">
                                            <td>{{$order->orderId}}</td>
                                            <td>{{empty($order->goods) ? '【商品已删除】' : $order->goods->name}}</td>
                                            <td>￥{{$order->totalPrice}}</td>
                                            <td>{{$order->created_at}}</td>
                                            <td>
                                                @if(!$order->is_expire)
                                                    @if($order->status == -1)
                                                        <a href="javascript:;" class="btn btn-sm default disabled"> {{trans('home.invoice_table_closed')}} </a>
                                                    @elseif($order->status == 0)
                                                        <a href="javascript:;" class="btn btn-sm dark disabled"> {{trans('home.invoice_table_wait_payment')}} </a>
                                                        @if(!empty($order->payment))
                                                            <a href="{{url('payment/' . $order->payment->sn)}}" target="_self" class="btn btn-sm red">支付</a>
                                                        @endif
                                                    @elseif($order->status == 1)
                                                        <a href="javascript:;" class="btn btn-sm dark disabled"> {{trans('home.invoice_table_wait_confirm')}} </a>
                                                    @elseif($order->status == 2)
                                                        <a href="javascript:;" class="btn btn-sm green disabled"> {{trans('home.invoice_table_wait_active')}} </a>
                                                    @else
                                                        <a href="javascript:;" class="btn btn-sm default disabled"> {{trans('home.invoice_table_expired')}} </a>
                                                    @endif
                                                @else
                                                    <a href="javascript:;" class="btn btn-sm default disabled"> {{trans('home.invoice_table_expired')}} </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $orderList->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script type="text/javascript">
        //
    </script>
@endsection
