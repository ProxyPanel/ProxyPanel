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
                            <span class="caption-subject bold uppercase"> 在线IP监控<small>（实时）</small> </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 col-sm-4 col-xs-12 form-control" name="id" value="{{Request::get('id')}}" id="id" placeholder="用户ID" onkeydown="if(event.keyCode==13){doSearch();}">
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 col-sm-4 col-xs-12 form-control" name="ip" value="{{Request::get('ip')}}" id="ip" placeholder="IP" onkeydown="if(event.keyCode==13){doSearch();}">
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 col-sm-4 col-xs-12 form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名" onkeydown="if(event.keyCode==13){doSearch();}">
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 col-sm-4 col-xs-12 form-control" name="port" value="{{Request::get('port')}}" id="port" placeholder="端口" onkeydown="if(event.keyCode==13){doSearch();}">
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
                                <button type="button" class="btn blue" onclick="doSearch();">查询</button>
                                <button type="button" class="btn grey" onclick="doReset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                    <tr>
                                        <th> # </th>
                                        <th> 时间 </th>
                                        <th> 类型 </th>
                                        <th> 节点 </th>
                                        <th> 用户 </th>
                                        <th> IP </th>
                                        <th> 归属地 </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($list->isEmpty())
                                        <tr>
                                            <td colspan="7" style="text-align: center;">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($list as $vo)
                                            <tr>
                                                <td>{{$vo->id}}</td>
                                                <td>{{$vo->created_at}}</td>
                                                <td>{{$vo->type}}</td>
                                                <td>{{$vo->node ? $vo->node->name : '【节点已删除】'}}</td>
                                                <td>{{$vo->user ? $vo->user->username : '【用户已删除】'}}</td>
                                                <td><a href="https://www.ipip.net/ip/{{$vo->ip}}.html" target="_blank">{{$vo->ip}}</a></td>
                                                <td>{{$vo->ipInfo}}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$list->total()}} 个账号</div>
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
    <script src="/assets/global/plugins/clipboardjs/clipboard.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        // 搜索
        function doSearch() {
            var id = $("#id").val();
            var ip = $("#ip").val();
            var username = $("#username").val();
            var port = $("#port").val();
            var nodeId = $("#nodeId option:checked").val();

            window.location.href = '{{url('admin/onlineIPMonitor')}}' + '?id=' + id + '&ip=' + ip + '&username=' + username + '&port=' + port + '&nodeId=' + nodeId;
        }

        // 重置
        function doReset() {
            window.location.href = '{{url('admin/onlineIPMonitor')}}';
        }
    </script>
@endsection