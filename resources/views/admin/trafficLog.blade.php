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
                            <span class="caption-subject bold uppercase"> 流量日志</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="port" value="{{Request::get('port')}}" id="port" placeholder="端口" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="user_id" value="{{Request::get('user_id')}}" id="user_id" placeholder="用户ID" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <button type="button" class="btn btn-sm blue" onclick="do_search();">查询</button>
                                <button type="button" class="btn btn-sm grey" onclick="do_reset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> 用户 </th>
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
                                            <td colspan="8">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($trafficLogList as $trafficLog)
                                            <tr class="odd gradeX">
                                                <td> {{$trafficLog->id}} </td>
                                                <td> <a href="{{url('admin/userList?port=') . $trafficLog->user->port}}" target="_blank"> <span class="label label-info"> {{$trafficLog->user->username}} </span> </a> </td>
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
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$trafficLogList->total()}} 条记录，合计 {{$totalTraffic}}</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
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
    <script type="text/javascript">
        // 搜索
        function do_search() {
            var port = $("#port").val();
            var user_id = $("#user_id").val();
            var username = $("#username").val();

            window.location.href = '{{url('admin/trafficLog')}}' + '?port=' + port + '&user_id=' + user_id + '&username=' + username;
        }

        // 重置
        function do_reset() {
            window.location.href = '{{url('admin/trafficLog')}}';
        }
    </script>
@endsection