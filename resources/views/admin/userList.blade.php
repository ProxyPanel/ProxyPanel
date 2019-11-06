@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">用户列表</h2>
                <div class="panel-actions">
                    <button class="btn btn-outline-default" onclick="exportSSJson()">
                        <i class="icon wb-download" aria-hidden="true"></i>导出JSON
                    </button>
                    <button class="btn btn-outline-default" onclick="batchAddUsers()">
                        <i class="icon wb-plus" aria-hidden="true"></i>批量生成
                    </button>
                    <button class="btn btn-outline-default" onclick="addUser()">
                        <i class="icon wb-user-add" aria-hidden="true"></i>添加用户
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-inline mb-20">
                    <div class="form-group">
                        <input type="text" class="form-control w-60" name="id" value="{{Request::get('id')}}" id="id" placeholder="ID">
                        <input type="text" class="form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名">
                        <input type="text" class="form-control w-100" name="wechat" value="{{Request::get('wechat')}}" id="wechat" placeholder="微信">
                        <input type="text" class="form-control w-100" name="qq" value="{{Request::get('qq')}}" id="qq" placeholder="QQ">
                        <input type="text" class="form-control w-60" name="port" value="{{Request::get('port')}}" id="port" placeholder="端口">
                        <select name="pay_way" id="pay_way" class="form-control">
                            <option value="" @if(Request::get('pay_way') == '') selected hidden @endif>付费方式</option>
                            <option value="0" @if(Request::get('pay_way') == '0') selected hidden @endif>免费</option>
                            <option value="1" @if(Request::get('pay_way') == '1') selected hidden @endif>月付</option>
                            <option value="2" @if(Request::get('pay_way') == '2') selected hidden @endif>季付</option>
                            <option value="3" @if(Request::get('pay_way') == '3') selected hidden @endif>半年付</option>
                            <option value="4" @if(Request::get('pay_way') == '4') selected hidden @endif>年付</option>
                        </select>
                        <select name="status" id="status" class="form-control">
                            <option value="" @if(Request::get('status') == '') selected hidden @endif>账号状态</option>
                            <option value="-1" @if(Request::get('status') == '-1') selected hidden @endif>禁用</option>
                            <option value="0" @if(Request::get('status') == '0') selected hidden @endif>未激活</option>
                            <option value="1" @if(Request::get('status') == '1') selected hidden @endif>正常</option>
                        </select>
                        <select name="enable" id="enable" class="form-control">
                            <option value="" @if(Request::get('enable') == '') selected hidden @endif>代理状态</option>
                            <option value="1" @if(Request::get('enable') == '1') selected hidden @endif>启用</option>
                            <option value="0" @if(Request::get('enable') == '0') selected hidden @endif>禁用</option>
                        </select>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-primary" onclick="doSearch()">搜索</button>
                        <button class="btn btn-danger" onclick="doReset()">重置</button>
                    </div>
                </div>
                <table class="text-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户名</th>
                        <th> 端口</th>
                        <th> 订阅码</th>
                        <th> 加密方式</th>
                        <!--<th> 协议 </th>
                        <th> 混淆 </th>-->
                        <th> 流量使用</th>
                        <th> 最后使用</th>
                        <th> 有效期</th>
                        <th> 状态</th>
                        <th> 代理</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody class="table-striped">
                    @if ($userList->isEmpty())
                        <tr>
                            <td colspan="12">暂无数据</td>
                        </tr>
                    @else
                        @foreach ($userList as $user)
                            <tr class="{{$user->trafficWarning ? 'red-700' : ''}}">
                                <td> {{$user->id}} </td>
                                <td> {{$user->username}} </td>
                                <td>
                                    @if ($user->port)
                                        {{$user->port}}
                                    @else
                                        <span class="badge badge-lg badge-danger"> 未分配 </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="javascript:" class="copySubscribeLink" data-clipboard-action="copy" data-clipboard-text="{{$user->link}}">{{$user->subscribe->code}}</a>
                                </td>
                                <td> {{$user->method}} </td>
                            <!--<td> {{$user->protocol}} </td>
                                <td> {{$user->obfs}} </td>-->
                                <td class="center"> {{$user->used_flow}} / {{$user->transfer_enable}} </td>
                                <td class="center"> {{empty($user->t) ? '未使用' : date('Y-m-d H:i', $user->t)}} </td>
                                <td class="center">
                                    @if ($user->expireWarning == '-1')
                                        <span class="badge badge-lg badge-danger"> {{$user->expire_time}} </span>
                                    @elseif ($user->expireWarning == '0')
                                        <span class="badge badge-lg badge-warning"> {{$user->expire_time}} </span>
                                    @elseif ($user->expireWarning == '1')
                                        <span class="badge badge-lg badge-default"> {{$user->expire_time}} </span>
                                    @else
                                        {{$user->expire_time}}
                                    @endif
                                </td>
                                <td>
                                    @if ($user->status > 0)
                                        <span class="badge badge-lg badge-primary"><i class="wb-check"></i></span>
                                    @elseif ($user->status < 0)
                                        <span class="badge badge-lg badge-danger"><i class="wb-close"></i></span>
                                    @else
                                        <span class="badge badge-lg badge-default"><i class="wb-minus"></i></span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->enable)
                                        <span class="badge badge-lg badge-info"><i class="wb-check"></i></span>
                                    @else
                                        <span class="badge badge-lg badge-danger"><i class="wb-close"></i></span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="javascript:editUser('{{$user->id}}');" class="btn btn-primary"><i class="icon wb-edit"></i></a>
                                        <a href="javascript:delUser('{{$user->id}}');" class="btn btn-danger"><i class="icon wb-trash"></i></a>
                                        <a href="javascript:doExport('{{$user->id}}');" class="btn btn-primary"><i class="icon wb-code"></i></a>
                                        <a href="javascript:doMonitor('{{$user->id}}');" class="btn btn-primary"><i class="icon wb-stats-bars"></i></a>
                                        <a href="javascript:ipMonitor('{{$user->id}}');" class="btn btn-primary"><i class="icon wb-check"></i></a>
                                        <a href="javascript:resetTraffic('{{$user->id}}');" class="btn btn-primary"><i class="icon wb-loop"></i>
                                        </a>
                                        <a href="javascript:switchToUser('{{$user->id}}');" class="btn btn-primary"><i class="icon wb-user"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 {{$userList->total()}} 个账号
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $userList->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="/assets/custom/Plugin/clipboardjs/clipboard.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        // 导出原版json配置
        function exportSSJson() {
            swal.fire({
                title: '导出成功',
                text: '成功导出原版SS的用户配置信息，加密方式为系统默认的加密方式',
                type: 'success',
                timer: 1300,
                showConfirmButton: false,
            }).then(() => window.location.href = '/admin/exportSSJson')
        }

        // 批量生成账号
        function batchAddUsers() {
            swal.fire({
                title: '注意',
                text: '将自动生成5个账号，确定继续吗？',
                type: 'question',
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/admin/batchAddUsers", {_token: '{{csrf_token()}}'}, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => window.location.reload())
                        } else {
                            swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                        }
                    });
                }
            });
        }

        // 添加账号
        function addUser() {
            window.location.href = '/admin/addUser';
        }

        // 编辑账号
        function editUser(id) {
            window.location.href = '/admin/editUser?id=' + id;
        }

        // 删除账号
        function delUser(id) {
            swal.fire({
                title: '警告',
                text: '确定删除账号？',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/admin/delUser", {id: id, _token: '{{csrf_token()}}'}, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => window.location.reload())
                        } else {
                            swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                        }
                    });
                }
            });
        }

        // 重置
        function doReset() {
            window.location.href = '/admin/userList';
        }

        // 导出配置
        function doExport(id) {
            window.location.href = '/admin/export?id=' + id;
        }

        // 流量监控
        function doMonitor(id) {
            window.location.href = '/admin/userMonitor?id=' + id;
        }

        //在线巡查
        function ipMonitor(id) {
            window.location.href = '/admin/onlineIPMonitor?id=' + id;
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

            window.location.href = '/admin/userList' + '?id=' + id + '&username=' + username + '&wechat=' + wechat + '&qq=' + qq + '&port=' + port + '&pay_way=' + pay_way + '&status=' + status + '&enable=' + enable;
        }

        // 重置流量
        function resetTraffic(id) {
            swal.fire({
                title: '警告',
                text: '确定重置该用户流量吗？',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/admin/resetUserTraffic", {_token: '{{csrf_token()}}', id: id}, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => window.location.reload())
                        } else {
                            swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                        }
                    });
                }
            });
        }

        // 切换用户身份
        function switchToUser(user_id) {
            $.ajax({
                'url': "/admin/switchToUser",
                'data': {
                    'user_id': user_id,
                    '_token': '{{csrf_token()}}'
                },
                'dataType': "json",
                'type': "POST",
                success: function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.href = "/")
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                }
            });
        }

        const clipboard = new ClipboardJS('.copySubscribeLink');
        clipboard.on('success', function () {
            swal.fire({
                title: '复制成功',
                type: 'success',
                timer: 1300,
                showConfirmButton: false
            });
        });
        clipboard.on('error', function () {
            swal.fire({
                title: '复制失败，请手动复制',
                type: 'error',
                timer: 1500,
                showConfirmButton: false
            });
        });
    </script>
@endsection
