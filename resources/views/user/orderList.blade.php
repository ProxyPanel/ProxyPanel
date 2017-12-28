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
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-wallet font-dark"></i>
                            <span class="caption-subject bold"> {{trans('home.invoice_title')}} </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th> # </th>
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
                                            <td>${{$order->totalPrice}}</td>
                                            <td>{{date('Y-m-d', strtotime($order->created_at))}}</td>
                                            <td>
                                                @if(!$order->is_expire)
                                                    @if($order->status == -1)
                                                        <span class="label label-default"> {{trans('home.invoice_table_closed')}} </span>
                                                    @elseif($order->status == 0)
                                                        <span class="label label-default"> {{trans('home.invoice_table_wait_payment')}} </span>
                                                    @elseif($order->status == 1)
                                                        <span class="label label-danger"> {{trans('home.invoice_table_wait_confirm')}} </span>
                                                    @elseif($order->status == 2)
                                                        <span class="label label-success"> {{trans('home.invoice_table_wait_active')}} </span>
                                                    @else
                                                        <span class="label label-default"> {{trans('home.invoice_table_expired')}} </span>
                                                    @endif
                                                @else
                                                    <span class="label label-default"> {{trans('home.invoice_table_expired')}} </span>
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
