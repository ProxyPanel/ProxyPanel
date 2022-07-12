@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <style>
        #swal2-content {
            display: grid !important;
        }

        .table a {
            text-decoration: none;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">节点列表</h3>
                @canany(['admin.node.geo', 'admin.node.create'])
                    <div class="panel-actions btn-group">
                        @can('admin.node.geo')
                            <button type="button" onclick="refreshGeo(0)" class="btn btn-info">
                                <i id="geo0" class="icon wb-map" aria-hidden="true"></i> 刷新【全部】节点地理信息
                            </button>
                        @endcan
                        @can('admin.node.create')
                            <a href="{{route('admin.node.create')}}" class="btn btn-primary"><i class="icon wb-plus"></i> 添加节点</a>
                        @endcan
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> ID</th>
                        <th> 类型</th>
                        <th> 名称</th>
                        <th> 域名</th>
                        <th> IP</th>
                        <th> 存活</th>
                        <th> 在线</th>
                        <th> 产生流量</th>
                        <th> 流量比例</th>
                        <th> 扩展</th>
                        <th> {{trans('common.status')}}</th>
                        <th> {{trans('common.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($nodeList as $node)
                        <tr>
                            <td> {{$node->id}} </td>
                            <td> {{$node->type_label}} </td>
                            <td> {{$node->name}} </td>
                            <td> {{$node->server}} </td>
                            <td> {{$node->is_ddns ? 'DDNS' : $node->ip}} </td>
                            <td> {{$node->uptime ?: '-'}} </td>
                            <td> {{$node->online_users ?: '-'}} </td>
                            <td> {{$node->transfer}} </td>
                            <td> {{$node->traffic_rate}} </td>
                            <td>
                                @isset($node->profile['passwd'])
                                    <span class="badge badge-lg badge-info"><i class="icon fas fa-stream"></i>单</span>
                                @endisset
                                @if($node->relay_node_id)
                                    <span class="badge badge-lg badge-info"><i class="icon fas fa-ethernet"></i>转</span>
                                @endif
                                @if(!$node->is_subscribe)
                                    <span class="badge badge-lg badge-danger"><i class="icon fas fa-rss"></i><del>订</del></span>
                                @endif
                            </td>
                            <td>
                                @if($node->isOnline)
                                    @if ($node->status)
                                        {{$node->load}}
                                    @else
                                        <i class="yellow-700 icon icon-spin fas fa-cog" aria-hidden="true"></i>
                                    @endif
                                @else
                                    @if ($node->status)
                                        <i class="red-600 icon fas fa-cog" aria-hidden="true"></i>
                                    @else
                                        <i class="red-600 icon fas fa-handshake-slash" aria-hidden="true"></i>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @canany(['admin.node.edit', 'admin.node.destroy', 'admin.node.monitor', 'admin.node.geo', 'admin.node.ping', 'admin.node.check', 'admin.node.reload'])
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-boundary="viewport" data-toggle="dropdown" aria-expanded="false">
                                        <i class="icon wb-wrench" aria-hidden="true"></i>
                                    </button>
                                    <div class="dropdown-menu" role="menu">
                                        @can('admin.node.edit')
                                            <a class="dropdown-item" href="{{route('admin.node.edit', [$node->id, 'page' => Request::query('page', 1)])}}" role="menuitem">
                                                <i class="icon wb-edit" aria-hidden="true"></i> 编辑
                                            </a>
                                        @endcan
                                        @can('admin.node.clone')
                                            <a class="dropdown-item" href="{{route('admin.node.clone', $node)}}" role="menuitem">
                                                <i class="icon wb-copy" aria-hidden="true"></i> 克隆
                                            </a>
                                        @endcan
                                        @can('admin.node.destroy')
                                            <a class="dropdown-item red-700" href="javascript:delNode('{{$node->id}}', '{{$node->name}}')" role="menuitem">
                                                <i class="icon wb-trash" aria-hidden="true"></i> 删除
                                            </a>
                                        @endcan
                                        @can('admin.node.monitor')
                                            <a class="dropdown-item" href="{{route('admin.node.monitor', $node)}}" role="menuitem">
                                                <i class="icon wb-stats-bars" aria-hidden="true"></i> 流量统计
                                            </a>
                                        @endcan
                                        <hr/>
                                        @can('admin.node.geo')
                                            <a class="dropdown-item" href="javascript:refreshGeo('{{$node->id}}')" role="menuitem">
                                                <i id="geo{{$node->id}}" class="icon wb-map" aria-hidden="true"></i> 刷新地理
                                            </a>
                                        @endcan
                                        @can('admin.node.ping')
                                            <a class="dropdown-item" href="javascript:pingNode('{{$node->id}}')" role="menuitem">
                                                <i id="ping{{$node->id}}" class="icon wb-order" aria-hidden="true"></i> 检测延迟
                                            </a>
                                        @endcan
                                        @can('admin.node.check')
                                            <a class="dropdown-item" href="javascript:checkNode('{{$node->id}}')" role="menuitem">
                                                <i id="node{{$node->id}}" class="icon wb-signal" aria-hidden="true"></i> 连通性检测
                                            </a>
                                        @endcan
                                        @if($node->type === 4)
                                            @can('admin.node.reload')
                                                <hr/>
                                                <a class="dropdown-item" href="javascript:reload('{{$node->id}}')" role="menuitem">
                                                    <i id="reload{{$node->id}}" class="icon wb-reload" aria-hidden="true"></i> 重载后端
                                                </a>
                                            @endcan
                                        @endif
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$nodeList->total()}}</code> 条线路
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$nodeList->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script>
        @can('admin.node.check')
        // 节点连通性测试
        function checkNode(id) {
            $.ajax({
                method: 'POST',
                url: '{{route('admin.node.check', '')}}/' + id,
                data: {_token: '{{csrf_token()}}'},
                beforeSend: function() {
                    $('#node' + id).removeClass('wb-signal').addClass('wb-loop icon-spin');
                },
                success: function(ret) {
                    if (ret.status === 'success') {
                        let str = '';
                        for (let i in ret.message) {
                            str += '<tr><td>' + i + '</td><td>' + ret.message[i][0] + '</td><td>' + ret.message[i][1] + '</td></tr>';
                        }
                        swal.fire({
                            title: ret.title,
                            icon: 'info',
                            html: '<table class="my-20"><thead class="thead-default"><tr><th> IP </th><th> ICMP </th> <th> TCP </th></thead><tbody>' + str + '</tbody></table>',
                            showConfirmButton: false,
                        });
                    } else {
                        swal.fire({title: ret.title, text: ret.message, icon: 'error'});
                    }
                },
                complete: function() {
                    $('#node' + id).removeClass('wb-loop icon-spin').addClass('wb-signal');
                },
            });
        }
        @endcan

        @can('admin.node.ping')
        // Ping节点获取延迟
        function pingNode(id) {
            $.ajax({
                method: 'POST',
                url: '{{route('admin.node.ping', '')}}/' + id,
                data: {_token: '{{csrf_token()}}'},
                beforeSend: function() {
                    $('#ping' + id).removeClass('wb-order').addClass('wb-loop icon-spin');
                },
                success: function(ret) {
                    if (ret.status === 'success') {
                        swal.fire({
                            icon: 'info',
                            html: ret.message,
                            showConfirmButton: false,
                        });
                    } else {
                        swal.fire({title: ret.message, icon: 'error'});
                    }
                },
                complete: function() {
                    $('#ping' + id).removeClass('wb-loop icon-spin').addClass('wb-order');
                },
            });
        }
        @endcan

        @can('admin.node.reload')
        // 发送节点重载请求
        function reload(id) {
            swal.fire({
                text: '确定重载节点?',
                icon: 'question',
                showCancelButton: true,
                cancelButtonText: '{{trans('common.close')}}',
                confirmButtonText: '{{trans('common.confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        method: 'POST',
                        url: '{{route('admin.node.reload', '')}}/' + id,
                        data: {_token: '{{csrf_token()}}'},
                        beforeSend: function() {
                            $('#reload' + id).removeClass('wb-reload').addClass('wb-loop icon-spin');
                        },
                        success: function(ret) {
                            if (ret.status === 'success') {
                                swal.fire({title: ret.message, icon: 'info', showConfirmButton: false});
                            } else {
                                swal.fire({title: ret.message, icon: 'error'});
                            }
                        },
                        complete: function() {
                            $('#reload' + id).removeClass('wb-loop icon-spin').addClass('wb-reload');
                        },
                    });
                }
            });
        }
        @endcan

        @can('admin.node.geo')
        // 刷新节点地理信息
        function refreshGeo(id) {
            $.ajax({
                method: 'GET',
                url: '{{route('admin.node.geo', '')}}/' + id,
                data: {_token: '{{csrf_token()}}'},
                beforeSend: function() {
                    $('#geo' + id).removeClass('wb-map').addClass('wb-loop icon-spin');
                },
                success: function(ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, icon: 'info', showConfirmButton: false});
                    } else {
                        swal.fire({title: ret.message, icon: 'error'});
                    }
                },
                complete: function() {
                    $('#geo' + id).removeClass('wb-loop icon-spin').addClass('wb-map');
                },
            });
        }
        @endcan

        @can('admin.node.destroy')
        // 删除节点
        function delNode(id, name) {
            swal.fire({
                title: '{{trans('common.warning')}}',
                text: '确定删除节点 【' + name + '】 ?',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: '{{trans('common.close')}}',
                confirmButtonText: '{{trans('common.confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        method: 'DELETE',
                        url: '{{route('admin.node.destroy', '')}}/' + id,
                        data: {_token: '{{csrf_token()}}'},
                        dataType: 'json',
                        success: function(ret) {
                            if (ret.status === 'success') {
                                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                            } else {
                                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                            }
                        },
                    });
                }
            });
        }
        @endcan
    </script>
@endsection
