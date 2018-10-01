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
                        <div class="row">
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <select class="form-control input-sm" name="is_expire" id="is_expire" onchange="doSearch()">
                                    <option value="" @if(Request::get('is_expire') == '') selected @endif>过期</option>
                                    <option value="0" @if(Request::get('is_expire') == '0') selected @endif>否</option>
                                    <option value="1" @if(Request::get('is_expire') == '1') selected @endif>是</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <select class="form-control input-sm" name="is_coupon" id="is_coupon" onchange="doSearch()">
                                    <option value="" @if(Request::get('is_coupon') == '') selected @endif>使用优惠券</option>
                                    <option value="0" @if(Request::get('is_coupon') == '0') selected @endif>否</option>
                                    <option value="1" @if(Request::get('is_coupon') == '1') selected @endif>是</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <select class="form-control input-sm" name="pay_way" id="pay_way" onchange="doSearch()">
                                    <option value="" @if(Request::get('pay_way') == '') selected @endif>支付方式</option>
                                    <option value="1" @if(Request::get('pay_way') == '1') selected @endif>余额支付</option>
                                    <option value="2" @if(Request::get('pay_way') == '2') selected @endif>有赞云支付</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <select class="form-control input-sm" name="status" id="status" onchange="doSearch()">
                                    <option value="" @if(Request::get('status') == '') selected @endif>订单状态</option>
                                    <option value="-1" @if(Request::get('status') == '-1') selected @endif>已关闭</option>
                                    <option value="0" @if(Request::get('status') == '0') selected @endif>待支付</option>
                                    <option value="1" @if(Request::get('status') == '1') selected @endif>已支付待确认</option>
                                    <option value="2" @if(Request::get('status') == '2') selected @endif>已完成</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <button type="button" class="btn btn-sm blue" onclick="doSearch();">查询</button>
                                <button type="button" class="btn btn-sm grey" onclick="doReset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                    <tr class="uppercase">
                                        <th> # </th>
                                        <th> 订单编号 </th>
                                        <th> 操作人 </th>
                                        <th> 商品 </th>
                                        <th> 过期时间 </th>
                                        <th> 优惠券 </th>
                                        <th> 原价 </th>
                                        <th> 实价 </th>
                                        <th> 支付方式 </th>
                                        <th> 订单状态 </th>
                                        <th> 创建时间 </th>
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
                                                <td> {{$order->order_sn}} </td>
                                                <td> {{$order->user ? $order->user->username : '【账号不存在】'}} </td>
                                                <td> {{$order->goods->name}} </td>
                                                <td> {{$order->is_expire ? '已过期' : $order->expire_at}} </td>
                                                <td> {{$order->coupon ? $order->coupon->name . ' - ' . $order->coupon->sn : ''}} </td>
                                                <td> ￥{{$order->origin_amount}} </td>
                                                <td> ￥{{$order->amount}} </td>
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
        // 搜索
        function doSearch() {
            var username = $("#username").val();
            var is_expire = $("#is_expire").val();
            var is_coupon = $("#is_coupon").val();
            var pay_way = $("#pay_way").val();
            var status = $("#status").val();

            window.location.href = '{{url('admin/orderList')}}' + '?username=' + username + '&is_expire=' + is_expire + '&is_coupon=' + is_coupon + '&pay_way=' + pay_way + '&status=' + status;
        }

        // 重置
        function doReset() {
            window.location.href = '{{url('admin/orderList')}}';
        }
    </script>
@endsection