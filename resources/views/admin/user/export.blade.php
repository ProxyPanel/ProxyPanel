@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title"> {{ trans('admin.user.proxies_config', ['username' => $user->username]) }}</h2>
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th>#</th>
                        <th>{{ trans('model.node.attribute') }}</th>
                        <th>{{ trans('admin.node.info.extend') }}</th>
                        <th>{{ trans('model.node.domain') }}</th>
                        <th>{{ trans('model.node.ipv4') }}</th>
                        <th>{{ trans('admin.user.proxy_info') }}</th>
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
                                @isset($node->profile['passwd'])
                                    {{-- 单端口 --}}
                                    <span class="badge badge-lg badge-info"><i
                                                class="fa-solid fa-arrows-left-right-to-line"
                                                aria-hidden="true"></i></span>
                                @endisset
                                @if($node->is_display === 0)
                                    {{-- 节点完全不可见 --}}
                                    <span class="badge badge-lg badge-danger"><i class="icon wb-eye-close"
                                                                                 aria-hidden="true"></i></span>
                                @elseif($node->is_display === 1)
                                    {{-- 节点只在页面中显示 --}}
                                    <span class="badge badge-lg badge-danger"><i class="fa-solid fa-link-slash"
                                                                                 aria-hidden="true"></i></span>
                                @elseif($node->is_display === 2)
                                    {{-- 节点只可被订阅到 --}}
                                    <span class="badge badge-lg badge-danger"><i class="fa-solid fa-store-slash"
                                                                                 aria-hidden="true"></i></span>
                                @endif
                            </td>
                            <td>{{$node->server}}</td>
                            <td>{{$node->ip}}</td>
                            <td>
                                @can('admin.user.exportProxy')
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-info"
                                                onclick="getInfo('{{$node->id}}','code')"><i
                                                    class="fa-solid fa-code"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info"
                                                onclick="getInfo('{{$node->id}}','qrcode')"><i
                                                    class="fa-solid fa-qrcode"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info"
                                                onclick="getInfo('{{$node->id}}','text')"><i
                                                    class="fa-solid fa-list"></i>
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
    <script src="/assets/custom/easy.qrcode.min.js" type="text/javascript"></script>
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
                              '<a href="' + ret.data +
                              '" class="btn btn-block btn-danger mt-4">{{ trans('common.open') }} ' +
                              ret.title + '</a>',
                          showConfirmButton: false,
                        });
                        break;
                      case 'qrcode':
                        swal.fire({
                          title: '{{trans('user.scan_qrcode')}}',
                          html: '<div id="qrcode"></div><button class="btn btn-block btn-outline-primary mt-4" onclick="Download()"> <i class="icon wb-download"></i> {{trans('common.download')}}</button>',
                          onBeforeOpen: () => {
                            new QRCode(document.getElementById('qrcode'), {text: ret.data});
                          },
                          showConfirmButton: false,
                        });
                        break;
                      case 'text':
                        swal.fire({
                          title: '{{trans('user.node.info')}}',
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

          function Download() {
            const canvas = document.getElementsByTagName('canvas')[0];
            canvas.toBlob((blob) => {
              let link = document.createElement('a');
              link.download = 'qr.png';

              let reader = new FileReader();
              reader.readAsDataURL(blob);
              reader.onload = () => {
                link.href = reader.result;
                link.click();
              };
            }, 'image/png');
          }
        </script>
    @endcan
@endsection
