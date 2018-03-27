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
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase"> 有赞云回调日志 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> client_id </th>
                                    <th> 有赞订单ID </th>
                                    <th> kdt_id </th>
                                    <th> 应用名称 </th>
                                    <th> 模式 </th>
                                    <th> 消息详情 </th>
                                    <th> 发送次数 </th>
                                    <th> 鉴权标记 </th>
                                    <th> 状态 </th>
                                    <th> 是否测试 </th>
                                    <th> 类型 </th>
                                    <th> 版本 </th>
                                    <th> 创建时间 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($list->isEmpty())
                                    <tr>
                                        <td colspan="14" style="text-align: center;">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($list as $vo)
                                        <tr class="odd gradeX">
                                            <td> {{$vo->id}} </td>
                                            <td> {{$vo->client_id}} </td>
                                            <td> {{$vo->yz_id}} </td>
                                            <td> {{$vo->kdt_id}} </td>
                                            <td> {{$vo->kdt_name}} </td>
                                            <td> {{$vo->mode}} </td>
                                            <td> {{$vo->msg}} </td>
                                            <td> {{$vo->sendCount}} </td>
                                            <td> {{$vo->sign}} </td>
                                            <td> {{$vo->status}} </td>
                                            <td> {{$vo->test}} </td>
                                            <td> {{$vo->type}} </td>
                                            <td> {{$vo->version}} </td>
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
        //
    </script>
@endsection
