@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/fonts/font-awesome/font-awesome.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">【{{$user->email}}】连接配置信息</h2>
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th>#</th>
                        <th>节点</th>
                        <th>扩展</th>
                        <th>域名</th>
                        <th>IPv4</th>
                        <th>配置信息</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($nodeList as $node)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                @can('admin.node.edit')
                                    <a href="{{route('admin.node.edit', $node)}}" target="_blank"> {{$node->name}} </a>
                                @else
                                    {{$node->name}}
                                @endcan
                            </td>
                            <td>
                                @if($node->compatible) <span class="label label-info">兼</span> @endif
                                @if($node->single) <span class="label label-danger">单</span> @endif
                                @if($node->ipv6) <span class="label label-danger">IPv6</span> @endif
                            </td>
                            <td>{{$node->server}}</td>
                            <td>{{$node->ip}}</td>
                            <td>
                                @can('admin.user.exportProxy')
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-info"
                                                onclick="getInfo('{{$node->id}}','code')"><i class="icon fa-code"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info"
                                                onclick="getInfo('{{$node->id}}','qrcode')"><i class="icon fa-qrcode"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info"
                                                onclick="getInfo('{{$node->id}}','text')"><i class="icon fa-list"></i>
                                        </button>
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
                        共 <code>{{$nodeList->total()}}</code> 个账号
                    </div>

                    <nav class="Page navigation float-right">
                        {{$nodeList->links()}}
                    </nav>
                </div>
            </div>
        </div>
    </div>>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="/assets/custom/jquery-qrcode/jquery.qrcode.min.js"></script>
    <script src="/assets/global/js/Plugin/webui-popover.js"></script>
    @can('admin.user.exportProxy')
        <script>
          function getInfo(id, type) {
            $.post("{{route('admin.user.exportProxy', $user)}}", {_token: '{{csrf_token()}}', id: id, type: type},
                function(ret) {
                  if (ret.status === 'success') {
                    switch (type) {
                      case 'code':
                        swal.fire({
                          html: '<textarea class="form-control" rows="8" readonly="readonly">' + ret.data +
                              '</textarea>' +
                              '<a href="' + ret.data + '" class="btn btn-danger btn-block mt-10">打开' +
                              ret.title + '</a>',
                          showConfirmButton: false,
                        });
                        break;
                      case 'qrcode':
                        swal.fire({
                          title: '{{trans('home.scan_qrcode')}}',
                          html: '<div id="qrcode"></div>',
                          onBeforeOpen: () => {
                            $('#qrcode').qrcode({text: ret.data});
                          },
                          showConfirmButton: false,
                        });
                        break;
                      case 'text':
                        swal.fire({
                          title: '{{trans('home.setting_info')}}',
                          html: '<textarea class="form-control" rows="12" readonly="readonly">' + ret.data +
                              '</textarea>',
                          showConfirmButton: false,
                        });
                        break;
                      default:
                        swal.fire({title: ret.title, text: ret.data});
                    }
                  }
                });
          }
        </script>
    @endcan
@endsection
