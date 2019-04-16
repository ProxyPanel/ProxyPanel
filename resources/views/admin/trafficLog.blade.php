@extends('admin.layouts')
@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        input,select {
            margin-bottom: 5px;
        }
    </style>
@endsection
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
                            <span class="caption-subject bold uppercase"> 流量日志 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 form-control" name="port" value="{{Request::get('port')}}" id="port" placeholder="端口" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 form-control" name="user_id" value="{{Request::get('user_id')}}" id="user_id" placeholder="用户ID" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <select class="form-control" name="nodeId" id="nodeId" onChange="doSearch()">
                                    <option value="" @if(Request::get('nodeId') == '') selected @endif>选择节点</option>
                                    @foreach($nodeList as $node)
                                        <option value="{{$node->id}}" @if(Request::get('nodeId') == $node->id) selected @endif>{{$node->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <button type="button" class="btn blue" onclick="do_search();">查询</button>
                                <button type="button" class="btn grey" onclick="do_reset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
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
                                    @if($list->isEmpty())
                                        <tr>
                                            <td colspan="8" style="text-align: center;">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($list as $vo)
                                            <tr class="odd gradeX">
                                                <td> {{$vo->id}} </td>
                                                <td>
                                                    @if(empty($vo->user))
                                                        【账号已删除】
                                                    @else
                                                        <a href="{{url('admin/userList?id=') . $vo->user->id}}" target="_blank"> <span class="label label-info"> {{$vo->user->username}} </span> </a>
                                                    @endif
                                                </td>
                                                <td> {{$vo->node ? $vo->node->name : '【节点已删除】'}} </td>
                                                <td> {{$vo->rate}} </td>
                                                <td> {{$vo->u}} </td>
                                                <td> {{$vo->d}} </td>
                                                <td> <span class="label label-danger"> {{$vo->traffic}} </span> </td>
                                                <td> {{$vo->log_time}} </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$list->total()}} 条记录，合计 {{$totalTraffic}} </div>
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
        function do_search() {
            var port = $("#port").val();
            var user_id = $("#user_id").val();
            var username = $("#username").val();
            var nodeId = $("#nodeId option:checked").val();

            window.location.href = '{{url('admin/trafficLog')}}' + '?port=' + port + '&user_id=' + user_id + '&username=' + username + '&nodeId=' + nodeId;
        }

        // 重置
        function do_reset() {
            window.location.href = '{{url('admin/trafficLog')}}';
        }
    </script>
@endsection