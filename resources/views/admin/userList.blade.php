@extends('admin.layouts')

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
                <a href="{{url('admin')}}">管理中心</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/userList')}}">账号管理</a>
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
                            <i class="icon-users font-dark"></i>
                            <span class="caption-subject bold uppercase"> 账号管理</span>
                        </div>
                        <div class="actions">
                            <div class="btn-group">
                                <button class="btn sbold blue" onclick="addUser()"> 新增
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row" style="padding-bottom:5px;">
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="wechat" value="{{Request::get('wechat')}}" id="wechat" placeholder="微信" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="qq" value="{{Request::get('qq')}}" id="qq" placeholder="QQ" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="port" value="{{Request::get('port')}}" id="port" placeholder="端口" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <select class="form-control input-sm" name="pay_way" id="pay_way" onChange="do_search()">
                                    <option value="0" @if(empty(Request::get('pay_way'))) selected @endif>付费方式</option>
                                    <option value="1" @if(Request::get('pay_way') == '1') selected @endif>月付</option>
                                    <option value="2" @if(Request::get('pay_way') == '2') selected @endif>半年付</option>
                                    <option value="3" @if(Request::get('pay_way') == '3') selected @endif>年付</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <select class="form-control input-sm" name="enable" id="enable" onChange="do_search()">
                                    <option value="" @if(empty(Request::get('enable'))) selected @endif>状态</option>
                                    <option value="1" @if(Request::get('enable') == '1') selected @endif>启用</option>
                                    <option value="0" @if(Request::get('enable') == '0') selected @endif>禁用</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <button type="button" class="btn btn-sm blue" onclick="do_search();">查询</button>
                                <button type="button" class="btn btn-sm grey" onclick="do_reset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column" id="sample_1">
                                <thead>
                                <tr>
                                    <th> ID </th>
                                    <th> 用户名（昵称） </th>
                                    <th> 端口 </th>
                                    <th> 加密方式 </th>
                                    <th> 可用流量/已消耗 </th>
                                    <th> 最后使用 </th>
                                    <th> 有效期 </th>
                                    <th> 状态 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if ($userList->isEmpty())
                                        <tr>
                                            <td colspan="8">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach ($userList as $user)
                                            <tr class="odd gradeX">
                                            <td> {{$user->id}} </td>
                                            <td> {{$user->username}} </td>
                                            <td> <span class="label label-danger"> {{$user->port}} </span> </td>
                                            <td> <span class="label label-default"> {{$user->method}} </span> </td>
                                            <td class="center"> {{$user->transfer_enable}}/{{$user->used_flow}} </td>
                                            <td class="center"> {{empty($user->t) ? '未使用' : date('Y-m-d H:i:s', $user->t)}} </td>
                                            <td class="center"> {{$user->expire_time}} </td>
                                            <td>
                                                @if ($user->enable)
                                                    <span class="label label-info">启用</span>
                                                @else
                                                    <span class="label label-danger">禁用</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm blue btn-outline" onclick="editUser('{{$user->id}}')">编辑</button>
                                                <button type="button" class="btn btn-sm red btn-outline" onclick="delUser('{{$user->id}}')">删除</button>
                                                <button type="button" class="btn btn-sm green btn-outline" onclick="do_export('{{$user->id}}')">配置信息</button>
                                                <button type="button" class="btn btn-sm purple btn-outline" onclick="do_monitor('{{$user->id}}')">流量监控</button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-5 col-sm-5">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$userList->total()}} 个用户</div>
                            </div>
                            <div class="col-md-7 col-sm-7">
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
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 添加账号
        function addUser() {
            window.location.href = '{{url('admin/addUser')}}';
        }

        // 编辑账号
        function editUser(id) {
            window.location.href = '{{url('admin/editUser?id=')}}' + id + '&page=' + '{{Request::get('page', 1)}}';
        }

        // 删除账号
        function delUser(id) {
            var _token = '{{csrf_token()}}';

            bootbox.confirm({
                message: "确定删除账号？",
                buttons: {
                    confirm: {
                        label: '确定',
                        className: 'btn-success'
                    },
                    cancel: {
                        label: '取消',
                        className: 'btn-danger'
                    }
                },
                callback: function (result) {
                    if (result) {
                        $.post("{{url('admin/delUser')}}", {id:id, _token:_token}, function(ret){
                            if (ret.status == 'success') {
                                bootbox.alert(ret.message, function(){
                                    window.location.reload();
                                });
                            } else {
                                bootbox.alert(ret.message);
                            }
                        });
                    }
                }
            });
        }

        // 搜索
        function do_search() {
            var username = $("#username").val();
            var wechat = $("#wechat").val();
            var qq = $("#qq").val();
            var port = $("#port").val();
            var pay_way = $("#pay_way option:checked").val();
            var enable = $("#enable option:checked").val();

            window.location.href = '{{url('admin/userList')}}' + '?username=' + username + '&wechat=' + wechat + '&qq=' + qq + '&port=' + port + '&pay_way=' + pay_way + '&enable=' + enable;
        }

        // 重置
        function do_reset() {
            window.location.href = '{{url('admin/userList')}}';
        }

        // 导出配置
        function do_export(id) {
            window.location.href = '{{url('admin/export?id=')}}' + id;
        }

        // 流量监控
        function do_monitor(id) {
            window.location.href = '{{url('admin/monitor?id=')}}' + id;
        }
    </script>
@endsection