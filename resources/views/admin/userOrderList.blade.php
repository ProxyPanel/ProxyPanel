@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase"> 消费记录 </span>
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
                                <button type="button" class="btn btn-sm blue" onclick="doSearch();">查询</button>
                                <button type="button" class="btn btn-sm grey" onclick="doReset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> 用户名 </th>
                                    <th> 服务 </th>
                                    <th> 原价 </th>
                                    <th> 实付 </th>
                                    <th> 优惠券 </th>
                                    <th> 过期时间 </th>
                                    <th> 操作时间 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($list->isEmpty())
                                        <tr>
                                            <td colspan="8">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($list as $vo)
                                            <tr class="odd gradeX">
                                                <td> {{$vo->oid}} </td>
                                                <td> {{empty($vo->user) ? '【用户已删除】' : $vo->user->username}} </td>
                                                <td> {{empty($vo->goods) ? '【商品已删除】' : $vo->goods->name}} </td>
                                                <td> {{$vo->totalOriginalPrice}} </td>
                                                <td> {{$vo->totalPrice}} </td>
                                                <td> {{empty($vo->coupon) ? '' : $vo->coupon->name . ' - ' . $vo->coupon->sn}} </td>
                                                <td> {{$vo->is_expire ? '已过期' : $vo->expire_at}} </td>
                                                <td> {{$vo->created_at}} </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$list->total()}} 条记录</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $list->links() }}
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
        // 搜索
        function doSearch() {
            var username = $("#username").val();
            var is_expire = $("#is_expire").val();
            var is_coupon = $("#is_coupon").val();

            window.location.href = '{{url('admin/userOrderList')}}' + '?username=' + username + '&is_expire=' + is_expire + '&is_coupon=' + is_coupon;
        }

        // 重置
        function doReset() {
            window.location.href = '{{url('admin/userOrderList')}}';
        }
    </script>
@endsection