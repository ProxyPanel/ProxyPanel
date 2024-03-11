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
                <h3 class="panel-title">{{ trans('admin.menu.node.list') }}</h3>
                @canany(['admin.node.geo', 'admin.node.create'])
                    <div class="panel-actions btn-group">
                        @can('admin.node.reload')
                            @if($nodeList->where('type',4)->count())
                                <button type="button" onclick="reload(0)" class="btn btn-info">
                                    <i id="reload_0" class="icon wb-reload" aria-hidden="true"></i> {{ trans('admin.node.reload_all') }}
                                </button>
                            @endif
                        @endcan
                        @can('admin.node.geo')
                            <button type="button" onclick="refreshGeo(0)" class="btn btn-outline-default">
                                <i id="geo_0" class="icon wb-map" aria-hidden="true"></i> {{ trans('admin.node.refresh_geo_all') }}
                            </button>
                        @endcan
                        @can('admin.node.create')
                            <a href="{{route('admin.node.create')}}" class="btn btn-primary">
                                <i class="icon wb-plus"></i> {{ trans('common.add') }}
                            </a>
                        @endcan
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> ID</th>
                        <th> {{ trans('model.common.type') }}</th>
                        <th> {{ trans('model.node.name') }}</th>
                        <th> {{ trans('model.node.domain') }}</th>
                        <th> IP</th>
                        <th> {{ trans('model.node.static') }}</th>
                        <th> {{ trans('model.node.online_user') }}</th>
                        <th> {{ trans('model.node.data_consume') }}</th>
                        <th> {{ trans('model.node.data_rate') }}</th>
                        <th> {{ trans('model.common.extend') }}</th>
                        <th> {{ trans('common.status.attribute') }}</th>
                        <th> {{ trans('common.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($nodeList as $node)
                        <tr>
                            <td> {{$node->id}} </td>
                            <td> {{$node->type_label}} </td>
                            <td> {{$node->name}} </td>
                            <td> {{$node->server}} </td>
                            <td> {{$node->is_ddns ? trans('model.node.ddns') : $node->ip}} </td>
                            <td> {{$node->uptime ?: '-'}} </td>
                            <td> {{$node->online_users ?: '-'}} </td>
                            <td> {{$node->transfer}} </td>
                            <td> {{$node->traffic_rate}} </td>
                            <td>
                                @isset($node->profile['passwd'])
                                    {{-- 单端口 --}}
                                    <span class="badge badge-lg badge-info"><i class="fa-solid fa-1" aria-hidden="true"></i></span>
                                @endisset
                                @if($node->is_display === 0)
                                    {{-- 节点完全不可见 --}}
                                    <span class="badge badge-lg badge-danger"><i class="icon wb-eye-close" aria-hidden="true"></i></span>
                                @elseif($node->is_display === 1)
                                    {{-- 节点只在页面中显示 --}}
                                    <span class="badge badge-lg badge-danger"><i class="fa-solid fa-link-slash" aria-hidden="true"></i></span>
                                @elseif($node->is_display === 2)
                                    {{-- 节点只可被订阅到 --}}
                                    <span class="badge badge-lg badge-danger"><i class="fa-solid fa-store-slash" aria-hidden="true"></i></span>
                                @endif
                                @if($node->ip)
                                    <span class="badge badge-md badge-info"><i class="fa-solid fa-4" aria-hidden="true"></i></span>
                                @endif
                                @if($node->ipv6)
                                    <span class="badge badge-md badge-info"><i class="fa-solid fa-6" aria-hidden="true"></i></span>
                                @endif
                            </td>
                            <td>
                                @if($node->isOnline)
                                    @if ($node->status)
                                        {{$node->load}}
                                    @else
                                        <i class="yellow-700 icon icon-spin fa-solid fa-gear" aria-hidden="true"></i>
                                    @endif
                                @else
                                    @if ($node->status)
                                        <i class="red-600 fa-solid fa-gear" aria-hidden="true"></i>
                                    @else
                                        <i class="red-600 fa-solid fa-handshake-simple-slash" aria-hidden="true"></i>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @canany(['admin.node.edit', 'admin.node.clone', 'admin.node.destroy', 'admin.node.monitor', 'admin.node.geo', 'admin.node.ping', 'admin.node
                                .check', 'admin.node.reload'])
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-boundary="viewport" data-toggle="dropdown" aria-expanded="false">
                                        <i class="icon wb-wrench" aria-hidden="true"></i>
                                    </button>
                                    <div class="dropdown-menu" role="menu">
                                        @can('admin.node.edit')
                                            <a class="dropdown-item" href="{{route('admin.node.edit', [$node->id, 'page' => Request::query('page', 1)])}}" role="menuitem">
                                                <i class="icon wb-edit" aria-hidden="true"></i> {{ trans('common.edit') }}
                                            </a>
                                        @endcan
                                        @can('admin.node.clone')
                                            <a class="dropdown-item" href="{{route('admin.node.clone', $node)}}" role="menuitem">
                                                <i class="icon wb-copy" aria-hidden="true"></i> {{ trans('admin.clone') }}
                                            </a>
                                        @endcan
                                        @can('admin.node.destroy')
                                            <a class="dropdown-item red-700" href="javascript:delNode('{{$node->id}}', '{{$node->name}}')" role="menuitem">
                                                <i class="icon wb-trash" aria-hidden="true"></i> {{ trans('common.delete') }}
                                            </a>
                                        @endcan
                                        @can('admin.node.monitor')
                                            <a class="dropdown-item" href="{{route('admin.node.monitor', $node)}}" role="menuitem">
                                                <i class="icon wb-stats-bars" aria-hidden="true"></i> {{ trans('admin.node.traffic_monitor') }}
                                            </a>
                                        @endcan
                                        <hr/>
                                        @can('admin.node.geo')
                                            <a class="dropdown-item" href="javascript:refreshGeo('{{$node->id}}')" role="menuitem">
                                                <i id="geo{{$node->id}}" class="icon wb-map" aria-hidden="true"></i> {{ trans('admin.node.refresh_geo') }}
                                            </a>
                                        @endcan
                                        @can('admin.node.ping')
                                            <a class="dropdown-item" href="javascript:pingNode('{{$node->id}}')" role="menuitem">
                                                <i id="ping_{{$node->id}}" class="icon wb-order" aria-hidden="true"></i> {{ trans('admin.node.ping') }}
                                            </a>
                                        @endcan
                                        @can('admin.node.check')
                                            <a class="dropdown-item" href="javascript:checkNode('{{$node->id}}')" role="menuitem">
                                                <i id="node_{{$node->id}}" class="icon wb-signal" aria-hidden="true"></i> {{ trans('admin.node.connection_test') }}
                                            </a>
                                        @endcan
                                        @if($node->type === 4)
                                            @can('admin.node.reload')
                                                <hr/>
                                                <a class="dropdown-item" href="javascript:reload('{{$node->id}}')" role="menuitem">
                                                    <i id="reload_{{$node->id}}" class="icon wb-reload" aria-hidden="true"></i> {{ trans('admin.node.reload') }}
                                                </a>
                                            @endcan
                                        @endif
                                    </div>
                                @endcan
                            </td>
                        </tr>
                        @if (count($node->childNodes))
                            @foreach($node->childNodes as $childNode)
                                <tr class="bg-blue-grey-200 grey-700 table-borderless frontlin">
                                    <td></td>
                                    <td><i class="float-left fa-solid fa-right-left" aria-hidden="true"></i>
                                        <strong>{{ trans('model.node.transfer') }}</strong></td>
                                    <td> {{ $childNode->name }} </td>
                                    <td> {{ $childNode->server }} </td>
                                    <td> {{ $childNode->is_ddns ? trans('model.node.ddns') : $childNode->ip }} </td>
                                    <td colspan="2">
                                        @if($childNode->is_display === 0)
                                            {{-- 节点完全不可见 --}}
                                            <span class="badge badge-lg badge-danger"><i class="icon wb-eye-close" aria-hidden="true"></i></span>
                                        @elseif($childNode->is_display === 1)
                                            {{-- 节点只在页面中显示 --}}
                                            <span class="badge badge-lg badge-danger"><i class="fa-solid fa-link-slash" aria-hidden="true"></i></span>
                                        @elseif($childNode->is_display === 2)
                                            {{-- 节点只可被订阅到 --}}
                                            <span class="badge badge-lg badge-danger"><i class="fa-solid fa-store-slash" aria-hidden="true"></i></span>
                                        @endif
                                    </td>
                                    <td colspan="2">
                                        @if (!$childNode->status || !$node->status)
                                            <i class="red-600 fa-solid fa-handshake-simple-slash" aria-hidden="true"></i>
                                        @endif
                                    </td>
                                    <td colspan="3">
                                        @canany(['admin.node.edit', 'admin.node.clone', 'admin.node.destroy', 'admin.node.monitor', 'admin.node.geo', 'admin.node.ping', 'admin.node.check'])
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-boundary="viewport" data-toggle="dropdown" aria-expanded="false">
                                                <i class="icon wb-wrench" aria-hidden="true"></i>
                                            </button>
                                            <div class="dropdown-menu" role="menu">
                                                @can('admin.node.edit')
                                                    <a class="dropdown-item" href="{{route('admin.node.edit', [$childNode->id, 'page' => Request::query('page', 1)])}}"
                                                       role="menuitem">
                                                        <i class="icon wb-edit" aria-hidden="true"></i> {{ trans('common.edit') }}
                                                    </a>
                                                @endcan
                                                @can('admin.node.clone')
                                                    <a class="dropdown-item" href="{{route('admin.node.clone', $childNode)}}" role="menuitem">
                                                        <i class="icon wb-copy" aria-hidden="true"></i> {{ trans('admin.clone') }}
                                                    </a>
                                                @endcan
                                                @can('admin.node.destroy')
                                                    <a class="dropdown-item red-700" href="javascript:delNode('{{$childNode->id}}', '{{$childNode->name}}')" role="menuitem">
                                                        <i class="icon wb-trash" aria-hidden="true"></i> {{ trans('common.delete') }}
                                                    </a>
                                                @endcan
                                                @can('admin.node.monitor')
                                                    <a class="dropdown-item" href="{{route('admin.node.monitor', $childNode)}}" role="menuitem">
                                                        <i class="icon wb-stats-bars" aria-hidden="true"></i> {{ trans('admin.node.traffic_monitor') }}
                                                    </a>
                                                @endcan
                                                <hr/>
                                                @can('admin.node.geo')
                                                    <a class="dropdown-item" href="javascript:refreshGeo('{{$childNode->id}}')" role="menuitem">
                                                        <i id="geo_{{$childNode->id}}" class="icon wb-map" aria-hidden="true"></i> {{ trans('admin.node.refresh_geo') }}
                                                    </a>
                                                @endcan
                                                @can('admin.node.ping')
                                                    <a class="dropdown-item" href="javascript:pingNode('{{$childNode->id}}')" role="menuitem">
                                                        <i id="ping_{{$childNode->id}}" class="icon wb-order" aria-hidden="true"></i> {{ trans('admin.node.ping') }}
                                                    </a>
                                                @endcan
                                                @can('admin.node.check')
                                                    <a class="dropdown-item" href="javascript:checkNode('{{$childNode->id}}')" role="menuitem">
                                                        <i id="node_{{$childNode->id}}" class="icon wb-signal" aria-hidden="true"></i> {{ trans('admin.node.connection_test') }}
                                                    </a>
                                                @endcan
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.node.counts', ['num' => $nodeList->total()]) !!}
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
              $('#node_' + id).removeClass('wb-signal').addClass('wb-loop icon-spin');
            },
            success: function(ret) {
              if (ret.status === 'success') {
                let str = '';
                for (let i in ret.message) {
                  str += '<tr><td>' + i + '</td><td>' + ret.message[i][0] + '</td><td>' + ret.message[i][1] +
                      '</td></tr>';
                }
                swal.fire({
                  title: ret.title,
                  icon: 'info',
                  html: '<table class="my-20"><thead class="thead-default"><tr><th> IP </th><th> ICMP </th> <th> TCP </th></thead><tbody>' +
                      str + '</tbody></table>',
                  showConfirmButton: false,
                });
              } else {
                swal.fire({title: ret.title, text: ret.message, icon: 'error'});
              }
            },
            complete: function() {
              $('#node_' + id).removeClass('wb-loop icon-spin').addClass('wb-signal');
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
              $('#ping_' + id).removeClass('wb-order').addClass('wb-loop icon-spin');
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
              $('#ping_' + id).removeClass('wb-loop icon-spin').addClass('wb-order');
            },
          });
        }
        @endcan

        @can('admin.node.reload')
        // 发送节点重载请求
        function reload(id) {
          swal.fire({
            text: '{{ trans('admin.node.reload_confirm') }}',
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
                  $('#reload_' + id).removeClass('wb-reload').addClass('wb-loop icon-spin');
                },
                success: function(ret) {
                  if (ret.status === 'success') {
                    swal.fire({title: ret.message, icon: 'info', showConfirmButton: false});
                  } else {
                    swal.fire({title: ret.message, icon: 'error'});
                  }
                },
                complete: function() {
                  $('#reload_' + id).removeClass('wb-loop icon-spin').addClass('wb-reload');
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
              $('#geo_' + id).removeClass('wb-map').addClass('wb-loop icon-spin');
            },
            success: function(ret) {
              if (ret.status === 'success') {
                swal.fire({title: ret.message, icon: 'info', showConfirmButton: false});
              } else {
                swal.fire({title: ret.message, icon: 'error'});
              }
            },
            complete: function() {
              $('#geo_' + id).removeClass('wb-loop icon-spin').addClass('wb-map');
            },
          });
        }
        @endcan

        @can('admin.node.destroy')
        // 删除节点
        function delNode(id, name) {
          swal.fire({
            title: '{{trans('common.warning')}}',
            text: '{{ trans('admin.confirm.delete.0', ['attribute' => trans('model.node.attribute')]) }}' + name +
                '{{ trans('admin.confirm.delete.1') }}',
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
                    swal.fire({
                      title: ret.message,
                      icon: 'success',
                      timer: 1000,
                      showConfirmButton: false,
                    }).then(() => window.location.reload());
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
