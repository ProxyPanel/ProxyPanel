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
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('user/goodsList')}}">流量包</a>
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
                            <i class="icon-basket font-dark"></i>
                            <span class="caption-subject bold uppercase"> 流量包列表 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column" id="sample_1">
                                <thead>
                                <tr>
                                    <th> ID </th>
                                    <th> 名称 </th>
                                    <th> 图片 </th>
                                    <th> 内含流量 </th>
                                    <th> 售价 </th>
                                    <th> 所需积分 </th>
                                    <th> </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($goodsList->isEmpty())
                                    <tr>
                                        <td colspan="7">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($goodsList as $key => $goods)
                                        <tr class="odd gradeX">
                                            <td> {{$key + 1}} </td>
                                            <td> {{$goods->name}} </td>
                                            <td> @if($goods->logo) <a href="{{$goods->logo}}" class="fancybox"><img src="{{$goods->logo}}"/></a> @endif </td>
                                            <td> {{$goods->traffic}} </td>
                                            <td> {{$goods->price}} </td>
                                            <td> {{$goods->score}} </td>
                                            <td>
                                                <button type="button" class="btn btn-sm red btn-outline" onclick="buy('{{$goods->id}}')">购买</button>
                                                <button type="button" class="btn btn-sm blue btn-outline" onclick="exchange('{{$goods->id}}')">兑换</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-5 col-sm-5">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$goodsList->total()}} 个流量包</div>
                            </div>
                            <div class="col-md-7 col-sm-7">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $goodsList->links() }}
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

    <script type="text/javascript">
        function buy() {
            //
        }

        // 编辑商品
        function exchange(id) {
            //
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
