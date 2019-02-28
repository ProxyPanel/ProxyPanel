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
                            <span class="caption-subject bold uppercase"> 用户列表 </span>
                        </div>
                        <div class="actions">
                            <div class="btn-group btn-group-devided">
                                <button class="btn sbold red" onclick="exportSSJson()"> 导出JSON </button>
                                <button class="btn sbold blue" onclick="batchAddUsers()"> 批量生成 </button>
                                <button class="btn sbold blue" onclick="addUser()"> 添加用户 </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 col-sm-4 col-xs-12 form-control" name="id" value="{{Request::get('id')}}" id="id" placeholder="用户ID" onkeydown="if(event.keyCode==13){doSearch();}">
                            </div>
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
                                <select class="form-control" name="pay_way" id="pay_way" onChange="doSearch()">
                                    <option value="" @if(Request::get('pay_way') == '') selected @endif>付费方式</option>
                                    <option value="0" @if(Request::get('pay_way') == '0') selected @endif>免费</option>
                                    <option value="1" @if(Request::get('pay_way') == '1') selected @endif>月付</option>
                                    <option value="2" @if(Request::get('pay_way') == '2') selected @endif>季付</option>
                                    <option value="3" @if(Request::get('pay_way') == '3') selected @endif>半年付</option>
                                    <option value="4" @if(Request::get('pay_way') == '4') selected @endif>年付</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <select class="form-control" name="status" id="status" onChange="doSearch()">
                                    <option value="" @if(Request::get('status') == '') selected @endif>账号状态</option>
                                    <option value="-1" @if(Request::get('status') == '-1') selected @endif>禁用</option>
                                    <option value="0" @if(Request::get('status') == '0') selected @endif>未激活</option>
                                    <option value="1" @if(Request::get('status') == '1') selected @endif>正常</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <select class="form-control" name="enable" id="enable" onChange="doSearch()">
                                    <option value="" @if(Request::get('enable') == '') selected @endif>代理状态</option>
                                    <option value="1" @if(Request::get('enable') == '1') selected @endif>启用</option>
                                    <option value="0" @if(Request::get('enable') == '0') selected @endif>禁用</option>
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
                                    <th> 用户名 </th>
                                    <th> 订阅码 </th>
                                    <th> 端口 </th>
                                    <th> 连接密码 </th>
                                    <th> 加密方式 </th>
                                    <!--<th> 协议 </th>
                                    <th> 混淆 </th>-->
                                    <th> 已消耗 </th>
                                    <th> 最后使用 </th>
                                    <th> 有效期 </th>
                                    <th> 状态 </th>
                                    <th> 代理 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if ($userList->isEmpty())
                                        <tr>
                                            <td colspan="14" style="text-align: center;">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach ($userList as $user)
                                            <tr class="odd gradeX {{$user->trafficWarning ? 'danger' : ''}}">
                                                <td> {{$user->id}} </td>
                                                <td> {{$user->username}} </td>
                                                <td> <a href="javascript:;" class="copySubscribeLink" data-clipboard-text="{{$user->link}}" title="点击复制订阅链接">{{$user->subscribe->code}}</a> </td>
                                                <td> <span class="label label-danger"> {{$user->port ? $user->port : '未分配'}} </span> </td>
                                                <td> <span class="label label-default"> {{$user->passwd}} </span> </td>
                                                <td> <span class="label label-default"> {{$user->method}} </span> </td>
                                                <!--<td> <span class="label label-default"> {{$user->protocol}} </span> </td>
                                                <td> <span class="label label-default"> {{$user->obfs}} </span> </td>-->
                                                <td class="center"> {{$user->used_flow}} / {{$user->transfer_enable}} </td>
                                                <td class="center"> {{empty($user->t) ? '未使用' : date('Y-m-d H:i:s', $user->t)}} </td>
                                                <td class="center">
                                                    @if ($user->expireWarning == '-1')
                                                        <span class="label label-danger"> {{$user->expire_time}} </span>
                                                    @elseif ($user->expireWarning == '0')
                                                        <span class="label label-warning"> {{$user->expire_time}} </span>
                                                    @elseif ($user->expireWarning == '1')
                                                        <span class="label label-default"> {{$user->expire_time}} </span>
                                                    @else
                                                        {{$user->expire_time}}
                                                    @endif
                                                </td>
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
                                                    <div class="btn-group">
                                                        <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="javascript:;" aria-expanded="false"> 操作
                                                            <i class="fa fa-angle-down"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="javascript:editUser('{{$user->id}}');"> 编辑 </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:delUser('{{$user->id}}');"> 删除 </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:doExport('{{$user->id}}');"> 配置信息 </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:doMonitor('{{$user->id}}');"> 流量概况 </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:ipMonitor('{{$user->id}}');"> 在线巡查 </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:resetTraffic('{{$user->id}}');"> 流量清零 </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:switchToUser('{{$user->id}}');"> 切换身份 </a>
                                                            </li>
                                                        </ul>
                                                    </div>
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
        // 导出原版json配置
        function exportSSJson() {
            layer.msg("成功导出原版SS的用户配置信息，加密方式为系统默认的加密方式");
            window.location.href = '{{url('admin/exportSSJson')}}';
        }

        // 批量生成账号
        function batchAddUsers() {
            layer.confirm('将自动生成5个账号，确定继续吗？', {icon: 3, title:'注意'}, function(index) {
                $.post("{{url('admin/batchAddUsers')}}", {_token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                });

                layer.close(index);
            });
        }

        // 添加账号
        function addUser() {
            window.location.href = '{{url('admin/addUser')}}';
        }

        // 编辑账号
        function editUser(id) {
            window.location.href = '{{url('admin/editUser?id=')}}' + id;
        }

        // 删除账号
        function delUser(id) {
            layer.confirm('确定删除账号？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('admin/delUser')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                });

                layer.close(index);
            });
        }

        // 搜索
        function doSearch() {
            var id = $("#id").val();
            var username = $("#username").val();
            var wechat = $("#wechat").val();
            var qq = $("#qq").val();
            var port = $("#port").val();
            var pay_way = $("#pay_way option:checked").val();
            var status = $("#status option:checked").val();
            var enable = $("#enable option:checked").val();

            window.location.href = '{{url('admin/userList')}}' + '?id=' + id +'&username=' + username + '&wechat=' + wechat + '&qq=' + qq + '&port=' + port + '&pay_way=' + pay_way + '&status=' + status + '&enable=' + enable;
        }

        // 重置
        function doReset() {
            window.location.href = '{{url('admin/userList')}}';
        }

        // 导出配置
        function doExport(id) {
            window.location.href = '{{url('admin/export?id=')}}' + id;
        }

        // 流量监控
        function doMonitor(id) {
            window.location.href = '{{url('admin/userMonitor?id=')}}' + id;
        }

        function ipMonitor(id) {
            window.location.href = '{{url('admin/onlineIPMonitor?id=')}}' + id;
        }

        // 重置流量
        function resetTraffic(id) {
            layer.confirm('确定重置该用户流量吗？', {icon: 7, title:'警告'}, function(index) {
                $.post("{{url('admin/resetUserTraffic')}}", {_token:'{{csrf_token()}}', id:id}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                });

                layer.close(index);
            });
        }

        // 切换用户身份
        function switchToUser(user_id) {
            $.ajax({
                'url': "{{url("/admin/switchToUser")}}",
                'data': {
                    'user_id': user_id,
                    '_token': '{{csrf_token()}}'
                },
                'dataType': "json",
                'type': "POST",
                success: function (ret) {
                    layer.msg(ret.message, {time: 1000}, function () {
                        if (ret.status == 'success') {
                            window.location.href = "/";
                        }
                    });
                }
            });
        }

        // 修正table的dropdown
        $('.table-scrollable').on('show.bs.dropdown', function () {
            $('.table-scrollable').css( "overflow", "inherit" );
        });

        $('.table-scrollable').on('hide.bs.dropdown', function () {
            $('.table-scrollable').css( "overflow", "auto" );
        });

        // 复制订阅链接
        var clipboard = new Clipboard('.copySubscribeLink');
        clipboard.on('success', function(e) {
            layer.alert("成功复制该用户的订阅链接", {icon: 1, title:'提示'});
        });
        clipboard.on('error', function(e) {
            console.log(e);
        });

    </script>
@endsection