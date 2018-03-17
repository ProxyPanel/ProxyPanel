@extends('admin.layouts')

@section('css')
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase">订单列表</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                    <tr class="uppercase">
                                        <th> # </th>
                                        <th> 订单编号 </th>
                                        <th> 操作人 </th>
                                        <th> 商品 </th>
                                        <th> 优惠券 </th>
                                        <th> 原价 </th>
                                        <th> 实价 </th>
                                        <th> 过期时间 </th>
                                        <th> 支付方式 </th>
                                        <th> 订单状态 </th>
                                        <th> 操作 </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($orderList->isEmpty())
                                        <tr>
                                            <td colspan="11" style="text-align: center;">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($orderList as $order)
                                            <tr>
                                                <td> {{$order->oid}} </td>
                                                <td> {{$order->orderId}} </td>
                                                <td> {{$order->user->username}} </td>
                                                <td> {{$order->goods->name}} </td>
                                                <td> {{$order->coupon ? $order->coupon->name : ''}} </td>
                                                <td> ￥{{$order->totalOriginalPrice}} </td>
                                                <td> ￥{{$order->totalPrice}} </td>
                                                <td> {{$order->is_expire ? '已过期' : $order->expire_at}} </td>
                                                <td> {{$order->pay_way == '1' ? '余额支付' : '有赞云支付'}} </td>
                                                <td>
                                                    @if($order->status == '-1')
                                                        已关闭
                                                    @elseif ($order->status == '0')
                                                        待支付
                                                    @elseif ($order->status == '1')
                                                        已支付待确认
                                                    @else
                                                        已完成
                                                    @endif
                                                </td>
                                                <td> {{$order->created_at}} </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$orderList->total()}} 个订单</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $orderList->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 审核
        function doAudit(id) {
            window.open('{{url('admin/applyDetail?id=')}}' + id);
        }

        // 搜索
        function do_search() {
            var username = $("#username").val();
            var status = $("#status option:checked").val();

            window.location.href = '{{url('admin/applyList')}}' + '?username=' + username + '&status=' + status;
        }

        // 重置
        function do_reset() {
            window.location.href = '{{url('admin/applyList')}}';
        }
    </script>
@endsection