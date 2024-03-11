@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{!! trans('admin.node.auth.title') !!}</h2>
                @can('admin.node.auth.store')
                    <div class="panel-actions">
                        <button class="btn btn-primary" onclick="addAuth()">
                            <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                        </button>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> {{ trans('model.node.id') }}</th>
                        <th> {{ trans('model.common.type') }}</th>
                        <th> {{ trans('model.node.name') }}</th>
                        <th> {{ trans('model.node.domain') }}</th>
                        <th> {!! trans('model.node_auth.key') !!}</th>
                        <th> {{ trans('model.node_auth.secret') }}</th>
                        <th> {{trans('common.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($authorizations as $auth)
                        <tr>
                            <td> {{$auth->node_id}} </td>
                            <td> {{$auth->node->type_label}} </td>
                            <td> {{Str::limit($auth->node->name, 20)}} </td>
                            <td> {{$auth->node->server ?? $auth->node->ip ?? $auth->node->ipv6}} </td>
                            <td><span class="badge badge-lg badge-info"> {{$auth->key}} </span></td>
                            <td><span class="badge badge-lg badge-info"> {{$auth->secret}} </span></td>
                            <td>
                                <div class="btn-group">
                                    <button data-target="#install_{{$auth->node->type}}_{{$auth->id}}" data-toggle="modal" class="btn btn-primary">
                                        <i class="icon wb-code" aria-hidden="true"></i> {{ trans('admin.node.auth.deploy.attribute') }}
                                    </button>
                                    @can('admin.node.auth.update')
                                        <button onclick="refreshAuth('{{$auth->id}}')" class="btn btn-danger">
                                            <i class="icon wb-reload" aria-hidden="true"></i> {{ trans('admin.node.auth.reset_auth') }}
                                        </button>
                                    @endcan
                                    @can('admin.node.auth.destroy')
                                        <button onclick="deleteAuth('{{$auth->id}}')" class="btn btn-primary">
                                            <i class="icon wb-trash" aria-hidden="true"></i> {{ trans('common.delete') }}
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.node.auth.counts', ['num' => $authorizations->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $authorizations->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($authorizations as $auth)
        <div id="install_{{$auth->node->type}}_{{$auth->id}}" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
            <div class="modal-dialog modal-simple modal-center modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('common.close') }}">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">
                            {{ trans('admin.node.auth.deploy.title', ['type_label' => $auth->node->type_label]) }}
                        </h4>
                    </div>
                    <div class="modal-body">
                        @if($auth->node->type === 2)
                            <div class="alert alert-info text-break">
                                <div class="text-center red-700 mb-5">VNET-V2Ray</div>
                                (yum install curl 2> /dev/null || apt install curl 2> /dev/null) \<br>
                                && curl -L -s https://bit.ly/3oO3HZy \<br>
                                | WEB_API="{{sysConfig('web_api_url') ?: sysConfig('website_url')}}" \<br>
                                NODE_ID={{$auth->node->id}} \<br>
                                NODE_KEY={{$auth->key}} \<br>
                                bash
                                <br>
                                <br>
                                <div class="text-center red-700 mb-5">{{ trans('admin.node.auth.deploy.command') }}</div>
                                {{ trans('admin.node.auth.deploy.update') }}: {{ trans('admin.node.auth.deploy.same') }}
                                <br>
                                {{ trans('admin.node.auth.deploy.uninstall') }}: curl -L -s https://bit.ly/3oO3HZy |
                                bash -s -- --remove
                                <br>
                                {{ trans('admin.node.auth.deploy.start') }}: systemctl start vnet-v2ray
                                <br>
                                {{ trans('admin.node.auth.deploy.stop') }}: systemctl stop vnet-v2ray
                                <br>
                                {{ trans('admin.node.auth.deploy.status') }}: systemctl status vnet-v2ray
                                <br>
                                {{ trans('admin.node.auth.deploy.recent_logs') }}: journalctl -x -n 300 --no-pager -u
                                vnet-v2ray
                                <br>
                                {{ trans('admin.node.auth.deploy.real_time_logs') }}: journalctl -u vnet-v2ray -f
                            </div>
                            <div class="alert alert-info text-break">
                                <div class="text-center red-700 mb-5">V2Ray-Poseidon</div>
                                (yum install curl 2> /dev/null || apt install curl 2> /dev/null) \<br>
                                && curl -L -s https://bit.ly/2HswWko \<br>
                                | WEB_API="{{sysConfig('web_api_url') ?: sysConfig('website_url')}}" \<br>
                                NODE_ID={{$auth->node->id}} \<br>
                                NODE_KEY={{$auth->key}} \<br>
                                bash
                                <br>
                                <br>
                                <div class="text-center red-700 mb-5">{{ trans('admin.node.auth.deploy.command') }}</div>
                                {{ trans('admin.node.auth.deploy.update') }}: curl -L -s https://bit.ly/2HswWko | bash
                                <br>
                                {{ trans('admin.node.auth.deploy.uninstall') }}: curl -L -s https://mrw.so/5IHPR4 | bash
                                <br>
                                {{ trans('admin.node.auth.deploy.start') }}: systemctl start v2ray
                                <br>
                                {{ trans('admin.node.auth.deploy.stop') }}: systemctl stop v2ray
                                <br>
                                {{ trans('admin.node.auth.deploy.status') }}: systemctl status v2ray
                                <br>
                                {{ trans('admin.node.auth.deploy.recent_logs') }}: journalctl -x -n 300 --no-pager -u
                                v2ray
                                <br>
                                {{ trans('admin.node.auth.deploy.real_time_logs') }}: journalctl -u v2ray -f
                            </div>
                        @elseif($auth->node->type === 3)
                            @if(!$auth->node->server)
                                <h3>
                                    {!! trans('admin.node.auth.deploy.trojan_hint', ['url' => route('admin.node.edit', $auth->node)]) !!}
                                </h3>
                            @else
                                <div class="alert alert-info text-break">
                                    <div class="text-center red-700 mb-5">Trojan-Poseidon</div>
                                    (yum install curl 2> /dev/null || apt install curl 2> /dev/null) \<br>
                                    && curl -L -s https://mrw.so/6cMfGy \<br>
                                    | WEB_API="{{sysConfig('web_api_url') ?: sysConfig('website_url')}}" \<br>
                                    NODE_ID={{$auth->node->id}} \<br>
                                    NODE_KEY={{$auth->key}} \<br>
                                    NODE_HOST={{$auth->node->server}} \<br>
                                    bash
                                    <br>
                                    <br>
                                    <div class="text-center red-700 mb-5">{{ trans('admin.node.auth.deploy.command') }}</div>
                                    {{ trans('admin.node.auth.deploy.update') }}: curl -L -s https://mrw.so/6cMfGy |
                                    bash
                                    <br>
                                    {{ trans('admin.node.auth.deploy.uninstall') }}: curl -L -s https://mrw.so/5ulpvu |
                                    bash
                                    <br>
                                    {{ trans('admin.node.auth.deploy.start') }}: systemctl start trojanp
                                    <br>
                                    {{ trans('admin.node.auth.deploy.stop') }}: systemctl stop trojanp
                                    <br>
                                    {{ trans('admin.node.auth.deploy.status') }}: systemctl status trojanp
                                    <br>
                                    {{ trans('admin.node.auth.deploy.recent_logs') }}: journalctl -x -n 300 --no-pager
                                    -u trojanp
                                    <br>
                                    {{ trans('admin.node.auth.deploy.real_time_logs') }}: journalctl -u trojanp -f
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info text-break">
                                <div class="text-center red-700 mb-5">VNET</div>
                                (yum install curl 2> /dev/null || apt install curl 2> /dev/null) \<br>
                                && curl -L -s https://bit.ly/3828OP1 \<br>
                                | WEB_API="{{sysConfig('web_api_url') ?: sysConfig('website_url')}}" \<br>
                                NODE_ID={{$auth->node->id}} \<br>
                                NODE_KEY={{$auth->key}} \<br>
                                bash
                                <br>
                                <br>
                                <div class="text-center red-700 mb-5">{{ trans('admin.node.auth.deploy.command') }}</div>
                                {{ trans('admin.node.auth.deploy.update') }}: {{ trans('admin.node.auth.deploy.same') }}
                                <br>
                                {{ trans('admin.node.auth.deploy.uninstall') }}: curl -L -s https://bit.ly/3828OP1 |
                                bash -s -- --remove
                                <br>
                                {{ trans('admin.node.auth.deploy.start') }}: systemctl start vnet
                                <br>
                                {{ trans('admin.node.auth.deploy.stop') }}: systemctl stop vnet
                                <br>
                                {{ trans('admin.node.auth.deploy.restart') }}: systemctl restart vnet
                                <br>
                                {{ trans('admin.node.auth.deploy.status') }}: systemctl status vnet
                                <br>
                                {{ trans('admin.node.auth.deploy.recent_logs') }}: journalctl -x -n 300 --no-pager -u
                                vnet
                                <br>
                                {{ trans('admin.node.auth.deploy.real_time_logs') }}: journalctl -u vnet -f
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script>
      // 生成授权KEY
      @can('admin.node.auth.store')
      function addAuth() {
        swal.fire({
          title: '{{ trans('admin.hint') }}',
          text: '{{ trans('admin.node.auth.generating_all') }}',
          icon: 'info',
          showCancelButton: true,
          cancelButtonText: '{{ trans('common.close') }}',
          confirmButtonText: '{{ trans('common.confirm') }}',
        }).then((result) => {
          if (result.value) {
            $.post('{{route('admin.node.auth.store')}}', {_token: '{{csrf_token()}}'}, function(ret) {
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
            });
          }
        });
      }
      @endcan

      @can('admin.node.auth.destroy')
      // 删除授权
      function deleteAuth(id) {
        swal.fire({
          title: '{{ trans('admin.hint') }}',
          text: '{{ trans('admin.confirm.delete.0', ['attribute' => trans('model.node_auth.attribute')]) }}' + id +
              '{{ trans('admin.confirm.delete.1') }}',
          icon: 'info',
          showCancelButton: true,
          cancelButtonText: '{{trans('common.close')}}',
          confirmButtonText: '{{trans('common.confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'DELETE',
              url: '{{route('admin.node.auth.destroy', '')}}/' + id,
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

      @can('admin.node.auth.update')
      // 重置授权认证KEY
      function refreshAuth(id) {
        swal.fire({
          title: '{{ trans('admin.hint') }}',
          text: '{{ trans('admin.confirm.continues') }}',
          icon: 'info',
          showCancelButton: true,
          cancelButtonText: '{{trans('common.close')}}',
          confirmButtonText: '{{trans('common.confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'PUT',
              url: '{{route('admin.node.auth.update', '')}}/' + id,
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
