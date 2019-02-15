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
                            <span class="caption-subject bold uppercase"> 用户在线IP列表<small>（最近10分钟）</small> </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 col-sm-4 col-xs-12 form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名" onkeydown="if(event.keyCode==13){doSearch();}">
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 col-sm-4 col-xs-12 form-control" name="wechat" value="{{Request::get('wechat')}}" id="wechat" placeholder="微信" onkeydown="if(event.keyCode==13){doSearch();}">
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 col-sm-4 col-xs-12 form-control" name="qq" value="{{Request::get('qq')}}" id="qq" placeholder="QQ" onkeydown="if(event.keyCode==13){doSearch();}">
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 form-control" name="port" value="{{Request::get('port')}}" id="port" placeholder="端口" onkeydown="if(event.keyCode==13){doSearch();}">
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
                                        <th> 用户名 </th>
                                        <th> 端口 </th>
                                        <th> 状态 </th>
                                        <th> 代理 </th>
                                        <th> 连接IP </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($userList->isEmpty())
                                        <tr>
                                            <td colspan="5" style="text-align: center;">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach ($userList as $user)
                                            <tr class="odd gradeX">
                                                <td> {{$user->id}} </td>
                                                <td> {{$user->username}} </td>
                                                <td> <span class="label label-danger"> {{$user->port}} </span> </td>
                                                <td>
                                                    @if ($user->status > 0)
                                                        <span class="label label-info">正常</span>
                                                    @elseif ($user->status < 0)
                                                        <span class="label label-danger">禁用</span>
                                                    @else
                                                        <span class="label label-default">未激活</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($user->enable)
                                                        <span class="label label-info">启用</span>
                                                    @else
                                                        <span class="label label-danger">禁用</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!$user->onlineIPList->isEmpty())
                                                        <table class="table table-hover table-light">
                                                            <thead>
                                                                <tr>
                                                                    <th> 时间 </th>
                                                                    <th> 节点 </th>
                                                                    <th> 类型 </th>
                                                                    <th> IP </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($user->onlineIPList as $vo)
                                                                <tr>
                                                                    <td>{{$vo->created_at}}</td>
                                                                    <td>{{$vo->node ? $vo->node->name : '【节点已删除】'}}</td>
                                                                    <td>{{$vo->type}}</td>
                                                                    <td><a href="https://www.ipip.net/ip/{{$vo->ip}}.html" target="_blank">{{$vo->ip}}</a></td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$userList->total()}} 个账号</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $userList->links() }}
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
            var username = $("#username").val();
            var wechat = $("#wechat").val();
            var qq = $("#qq").val();
            var port = $("#port").val();

            window.location.href = '{{url('admin/userOnlineIPList')}}' + '?username=' + username + '&wechat=' + wechat + '&qq=' + qq + '&port=' + port;
        }

        // 重置
        function doReset() {
            window.location.href = '{{url('admin/userOnlineIPList')}}';
        }
    </script>
@endsection