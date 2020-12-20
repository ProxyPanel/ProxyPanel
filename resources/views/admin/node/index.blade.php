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
                            <button type="button" onclick="refreshGeo()" class="btn btn-info">
                                <i class="icon wb-map"></i> 刷新节点地理信息
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
                        <th> IP</th>
                        <th> 域名</th>
                        <th> 存活</th>
                        <th> 状态</th>
                        <th> 在线</th>
                        <th> 产生流量</th>
                        <th> 流量比例</th>
                        <th> 扩展</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($nodeList as $node)
                        <tr>
                            <td>
                                {{$node->id}}
                            </td>
                            <td>
                                @if($node->is_relay)
                                    中转
                                @else
                                    {{$node->type_label}}
                                @endif
                            </td>
                            <td> {{$node->name}} </td>
                            <td> {{$node->is_ddns ? 'DDNS' : $node->ip}} </td>
                            <td> {{$node->server}} </td>
                            <td> {{$node->uptime}} </td>
                            <td>
                                @if(!$node->isOnline)
                                    <i class="red-600 icon wb-warning" aria-hidden="true"></i>
                                @elseif (!$node->status)
                                    <i class="yellow-600 icon wb-warning" aria-hidden="true"></i>
                                @endif
                                {{$node->status? $node->load : '维护'}}
                            </td>
                            <td> {{$node->online_users}} </td>
                            <td> {{$node->transfer}} </td>
                            <td> {{$node->traffic_rate}} </td>
                            <td>
                                @if($node->compatible) <span class="badge badge-lg badge-info">兼</span> @endif
                                @if($node->single) <span class="badge badge-lg badge-info">单</span> @endif
                                @if(!$node->is_subscribe)<span class="badge badge-lg badge-danger"><del>订</del></span> @endif
                            </td>
                            <td>
                                @canany(['admin.node.edit', 'admin.node.destroy', 'admin.node.monitor', 'admin.node.geo', 'admin.node.ping', 'admin.node.check', 'admin.node.reload'])
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-primary dropdown-toggle" data-boundary="viewport" data-toggle="dropdown" aria-expanded="false">
                                            <i class="icon wb-wrench" aria-hidden="true"></i>
                                        </button>
                                        <div class="dropdown-menu" role="menu">
                                            @can('admin.node.edit')
                                                <a class="dropdown-item" href="{{route('admin.node.edit', [$node->id, 'page' => Request::input('page', 1)])}}" role="menuitem">
                                                    <i class="icon wb-edit" aria-hidden="true"></i> 编辑
                                                </a>
                                            @endcan
                                            @can('admin.node.destroy')
                                                <a class="dropdown-item" href="javascript:delNode('{{$node->id}}', '{{$node->name}}')" role="menuitem">
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
                swal.fire({
                  title: ret.title,
                  icon: 'info',
                  html: '<table class="my-20"><thead class="thead-default"><tr><th> ICMP </th> <th> TCP </th></thead><tbody><tr><td>' +
                      ret.message[0] + '</td><td>' + ret.message[1] + '</td></tr></tbody></table>',
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
                  html: '<table class="my-20"><thead class="thead-default"><tr><th> 电信 </th> <th> 联通 </th> <th> 移动 </th> <th> 香港 </th></thead><tbody><tr><td>' +
                      ret.message[0] + '</td><td>' + ret.message[1] + '</td><td>' + ret.message[2] + '</td><td>' +
                      ret.message[3] + '</td></tr></tbody></table>',
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
            cancelButtonText: '{{trans('home.ticket_close')}}',
            confirmButtonText: '{{trans('home.ticket_confirm')}}',
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
        function refreshGeo(id = 0) {
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
            title: '警告',
            text: '确定删除节点 【' + name + '】 ?',
            icon: 'warning',
            showCancelButton: true,
            cancelButtonText: '{{trans('home.ticket_close')}}',
            confirmButtonText: '{{trans('home.ticket_confirm')}}',
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
