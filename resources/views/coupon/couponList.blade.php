@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet" type="text/css" />
    <style>
        .fancybox > img {
            width: 75px;
            height: 75px;
        }
    </style>
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('coupon/couponList')}}">卡券管理</a>
                <i class="fa fa-circle"></i>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-list font-dark"></i>
                            <span class="caption-subject bold uppercase"> 卡券列表 </span>
                        </div>
                        <div class="actions">
                            <div class="btn-group btn-group-devided" data-toggle="buttons">
                                <button class="btn sbold blue" onclick="exportCoupon()"> 批量导出
                                    <i class="fa fa-download"></i>
                                </button>
                                <button class="btn sbold blue" onclick="addCoupon()"> 生成
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                <tr>
                                    <th> ID </th>
                                    <th> 名称 </th>
                                    <th> LOGO </th>
                                    <th> 券码 </th>
                                    <th> 用途 </th>
                                    <th> 优惠 </th>
                                    <th> 有效期 </th>
                                    <th> 状态 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($couponList->isEmpty())
                                    <tr>
                                        <td colspan="9">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($couponList as $coupon)
                                        <tr class="odd gradeX">
                                            <td> {{$coupon->id}} </td>
                                            <td> {{$coupon->name}} </td>
                                            <td> @if($coupon->logo) <a href="{{$coupon->logo}}" class="fancybox"><img src="{{$coupon->logo}}"/></a> @endif </td>
                                            <td> <span class="label label-info">{{$coupon->sn}}</span> </td>
                                            <td> {{$coupon->usage == '1' ? '一次性' : '可重复'}} </td>
                                            <td>
                                                @if($coupon->type == '1')
                                                    {{$coupon->amount}}元
                                                @else
                                                    {{$coupon->discount * 10}}折
                                                @endif
                                            </td>
                                            <td> {{date('Y-m-d', $coupon->available_start)}} ~ {{date('Y-m-d', $coupon->available_end)}} </td>
                                            <td>
                                                @if ($coupon->usage == 1)
                                                    @if($coupon->status == '1')
                                                        <span class="label label-default"> 已使用 </span>
                                                    @elseif ($coupon->status == '2')
                                                        <span class="label label-default"> 已失效 </span>
                                                    @else
                                                        <span class="label label-success"> 未使用 </span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm red btn-outline" onclick="delCoupon('{{$coupon->id}}')">删除</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$couponList->total()}} 张优惠券</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $couponList->links() }}
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
    <script src="/assets/global/plugins/fancybox/source/jquery.fancybox.js" type="text/javascript"></script>
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 批量导出卡券
        function exportCoupon() {
            window.location.href = '{{url('coupon/exportCoupon')}}';
        }

        // 添加卡券
        function addCoupon() {
            window.location.href = '{{url('coupon/addCoupon')}}';
        }

        // 删除卡券
        function delCoupon(id) {
            layer.confirm('确定删除该卡券吗？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('coupon/delCoupon')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                });

                layer.close(index);
            });
        }

        // 查看商品图片
        $(document).ready(function () {
            $('.fancybox').fancybox({
                openEffect: 'elastic',
                closeEffect: 'elastic'
            })
        })
    </script>
@endsection
