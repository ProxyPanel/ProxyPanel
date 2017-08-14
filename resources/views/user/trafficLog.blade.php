@extends('user.layouts')

@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('user/trafficLog')}}">流量日志</a>
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
                            <i class="icon-speedometer font-dark"></i>
                            <span class="caption-subject bold uppercase"> 流量日志</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column" id="sample_1">
                                <thead>
                                <tr>
                                    <th> ID </th>
                                    <th> 节点 </th>
                                    <th> 流量比例 </th>
                                    <th> 上传流量 </th>
                                    <th> 下载流量 </th>
                                    <th> 总流量 </th>
                                    <th> 记录时间 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($trafficLogList->isEmpty())
                                        <tr>
                                            <td colspan="9">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($trafficLogList as $trafficLog)
                                            <tr class="odd gradeX">
                                                <td> {{$trafficLog->id}} </td>
                                                <td> {{$trafficLog->ssnode->name}} </td>
                                                <td> {{$trafficLog->rate}} </td>
                                                <td> {{$trafficLog->u}} </td>
                                                <td> {{$trafficLog->d}} </td>
                                                <td> <span class="label label-danger"> {{$trafficLog->traffic}} </span> </td>
                                                <td> {{$trafficLog->log_time}} </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-5 col-sm-5">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$trafficLogList->total()}} 条记录</div>
                            </div>
                            <div class="col-md-7 col-sm-7">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $trafficLogList->links() }}
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
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

@endsection