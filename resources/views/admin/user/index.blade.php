@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/custom/range.min.css" rel="stylesheet">
    <style>
        #swal2-content {
            display: grid !important;
        }

        .table a {
            color: #76838f;
            text-decoration: none;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.menu.user.list') }}</h2>
                @canany(['admin.user.batch', 'admin.user.create'])
                    <div class="panel-actions">
                        @can('admin.user.batch')
                            <button class="btn btn-outline-default" onclick="batchAddUsers()">
                                <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('admin.user.massive.button') }}
                            </button>
                        @endcan
                        @can('admin.user.create')
                            <a href="{{route('admin.user.create')}}" class="btn btn-outline-primary">
                                <i class="icon wb-user-add" aria-hidden="true"></i> {{ trans('common.add') }}
                            </a>
                        @endcan
                    </div>
                @endcanany
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-xxl-1 col-lg-1 col-md-1 col-sm-4">
                        <input type="number" class="form-control" name="id" value="{{Request::query('id')}}" placeholder="{{ trans('model.user.id') }}"/>
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('validation.attributes.username') }}"/>
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <input type="text" class="form-control" name="wechat" value="{{Request::query('wechat')}}" placeholder="{{ trans('model.user.wechat') }}"/>
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <input type="number" class="form-control" name="qq" value="{{Request::query('qq')}}" placeholder="{{ trans('model.user.qq') }}"/>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-2 col-md-2 col-sm-4">
                        <input type="number" class="form-control" name="port" value="{{Request::query('port')}}" placeholder="{{ trans('model.user.port') }}"/>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="user_group_id" name="user_group_id">
                            <option value="" hidden>{{ trans('model.user.group') }}</option>
                            @foreach($userGroups as $key => $group)
                                <option value="{{$key}}">{{$group}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="level" name="level">
                            <option value="" hidden>{{ trans('model.user.level') }}</option>
                            @foreach($levels as $key => $level)
                                <option value="{{$key}}">{{$level}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="status" name="status">
                            <option value="" hidden>{{ trans('model.user.account_status') }}</option>
                            <option value="-1">{{ trans('common.status.banned') }}</option>
                            <option value="0">{{ trans('common.status.inactive') }}</option>
                            <option value="1">{{ trans('common.status.normal') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="enable" name="enable">
                            <option value="" hidden>{{ trans('model.user.proxy_status') }}</option>
                            <option value="1">{{ trans('common.status.enabled') }}</option>
                            <option value="0">{{ trans('common.status.banned') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.user.index')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> @sortablelink('id', '#')</th>
                        <th> {{ trans('validation.attributes.username') }}</th>
                        <th> @sortablelink('credit', trans('model.user.credit'))</th>
                        <th> @sortablelink('port', trans('model.user.port'))</th>
                        <th> {{ trans('model.subscribe.code') }}</th>
                        <th> {{ trans('model.user.traffic_used') }}</th>
                        <th> @sortablelink('t', trans('common.latest_at'))</th>
                        <th> @sortablelink('expired_at', trans('common.expired_at'))</th>
                        <th> {{ trans('common.account') }}</th>
                        <th> {{ trans('model.user.service') }}</th>
                        <th> {{ trans('common.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($userList as $user)
                        <tr class="{{$user->isTrafficWarning() ? 'table-danger' : ''}}">
                            <td> {{$user->id}} </td>
                            <td> {{$user->username}} </td>
                            <td> {{$user->credit}} </td>
                            <td>
                                {!! $user->port? : '<span class="badge badge-lg badge-danger"> '.trans('common.none').' </span>' !!}
                            </td>
                            <td>
                                <a href="javascript:" class="copySubscribeLink" data-clipboard-action="copy"
                                   data-clipboard-text="{{$user->subUrl()}}">{{$user->subscribe->code}}</a>
                            </td>
                            <td> {{flowAutoShow($user->used_traffic)}} / {{$user->transfer_enable_formatted}} </td>
                            <td> {{$user->t? date('Y-m-d H:i', $user->t): trans('common.status.unused')}} </td>
                            <td>
                                @if($user->expiration_status() !== 3)
                                    <span class="badge badge-lg badge-{{['danger','warning','default'][$user->expiration_status()]}}"> {{ $user->expiration_date }} </span>
                                @else
                                    {{ $user->expiration_date }}
                                @endif
                            </td>
                            <td>
                                @if ($user->status > 0)
                                    <span class="badge badge-lg badge-primary">
                                        <i class="wb-check" aria-hidden="true"></i>
                                    </span>
                                @elseif ($user->status < 0)
                                    <span class="badge badge-lg badge-danger">
                                        <i class="wb-close" aria-hidden="true"></i>
                                    </span>
                                @else
                                    <span class="badge badge-lg badge-default">
                                        <i class="wb-minus" aria-hidden="true"></i>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-lg badge-{{$user->enable?'info':'danger'}}">
                                    <i class="wb-{{$user->enable?'check':'close'}}" aria-hidden="true"></i>
                                </span>
                            </td>
                            <td>
                                @canany(['admin.user.edit', 'admin.user.destroy', 'admin.user.export', 'admin.user.monitor', 'admin.user.online', 'admin.user.reset', 'admin.user.switch'])
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-boundary="viewport" data-toggle="dropdown" aria-expanded="false">
                                        <i class="icon wb-wrench" aria-hidden="true"></i>
                                    </button>
                                    <div class="dropdown-menu" role="menu">
                                        @can('admin.user.edit')
                                            <a class="dropdown-item" href="{{route('admin.user.edit', ['user'=>$user->id, Request::getQueryString()])}}" role="menuitem">
                                                <i class="icon wb-edit" aria-hidden="true"></i> {{ trans('common.edit') }}
                                            </a>
                                        @endcan
                                        @can('admin.user.destroy')
                                            <a class="dropdown-item" href="javascript:delUser('{{route('admin.user.destroy', $user->id)}}','{{$user->username}}')" role="menuitem">
                                                <i class="icon wb-trash" aria-hidden="true"></i> {{ trans('common.delete') }}
                                            </a>
                                        @endcan
                                        @can('admin.user.export')
                                            <a class="dropdown-item" href="{{route('admin.user.export', $user)}}" role="menuitem">
                                                <i class="icon wb-code" aria-hidden="true"></i> {{ trans('admin.user.proxy_info') }}
                                            </a>
                                        @endcan
                                        @can('admin.user.monitor')
                                            <a class="dropdown-item" href="{{route('admin.user.monitor', $user)}}" role="menuitem">
                                                <i class="icon wb-stats-bars" aria-hidden="true"></i> {{ trans('admin.user.traffic_monitor') }}
                                            </a>
                                        @endcan
                                        @can('admin.user.online')
                                            <a class="dropdown-item" href="{{route('admin.user.online', $user)}}" role="menuitem">
                                                <i class="icon wb-cloud" aria-hidden="true"></i> {{ trans('admin.user.online_monitor') }}
                                            </a>
                                        @endcan
                                        @can('admin.user.reset')
                                            <a class="dropdown-item" href="javascript:resetTraffic('{{$user->id}}','{{$user->username}}')" role="menuitem">
                                                <i class="icon wb-reload" aria-hidden="true"></i> {{ trans('admin.user.reset_traffic') }}
                                            </a>
                                        @endcan
                                        @can('admin.user.switch')
                                            <a class="dropdown-item" href="javascript:switchToUser('{{$user->id}}')" role="menuitem">
                                                <i class="icon wb-user" aria-hidden="true"></i> {{ trans('admin.user.user_view') }}
                                            </a>
                                        @endcan
                                        @can('admin.user.VNetInfo')
                                            <a class="dropdown-item" href="javascript:VNetInfo('{{$user->id}}')" role="menuitem">
                                                <i id="vent_{{$user->id}}" class="icon wb-link-broken" aria-hidden="true"></i> {{ trans('admin.user.connection_test') }}
                                            </a>
                                        @endcan
                                    </div>
                                @endcanany
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.user.counts', ['num' => $userList->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$userList->links()}}
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
    <script src="/assets/custom/clipboardjs/clipboard.min.js"></script>
    <script>
      $(document).ready(function() {
        $('#user_group_id').val({{Request::query('user_group_id')}});
        $('#level').val({{Request::query('level')}});
        $('#status').val({{Request::query('status')}});
        $('#enable').val({{Request::query('enable')}});
        $('select').on('change', function() { this.form.submit(); });
      });

      @can('admin.user.batch')
      // 批量生成账号
      function batchAddUsers() {
        swal.fire({
          title: '{{ trans('admin.user.massive.text') }}',
          input: 'range',
          inputAttributes: {min: 1, max: 10},
          inputValue: 1,
          icon: 'question',
          showCancelButton: true,
          cancelButtonText: '{{ trans('common.close') }}',
          confirmButtonText: '{{ trans('common.confirm') }}',
        }).then((result) => {
          if (result.value) {
            $.post('{{route('admin.user.batch')}}', {_token: '{{csrf_token()}}', amount: result.value}, function(ret) {
              if (ret.status === 'success') {
                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
              } else {
                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
              }
            });
          }
        });
      }
      @endcan

      @can('admin.user.destroy')
      // 删除账号
      function delUser(url, username) {
        swal.fire({
          title: '{{trans('common.warning')}}',
          text: '{{ trans('admin.confirm.delete.0', ['attribute' => trans('model.user.attribute')]) }}' + username + '{{ trans('admin.confirm.delete.1') }}',
          icon: 'warning',
          showCancelButton: true,
          cancelButtonText: '{{ trans('common.close') }}',
          confirmButtonText: '{{ trans('common.confirm') }}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'DELETE',
              url: url,
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

      @can('admin.user.reset')
      // 重置流量
      function resetTraffic(id, username) {
        swal.fire({
          title: '{{trans('common.warning')}}',
          text: '{{ trans('admin.user.reset_confirm.0') }}' + username + '{{ trans('admin.user.reset_confirm.1') }}',
          icon: 'warning',
          showCancelButton: true,
          cancelButtonText: '{{ trans('common.close') }}',
          confirmButtonText: '{{ trans('common.confirm') }}',
        }).then((result) => {
          if (result.value) {
            $.post('{{route('admin.user.reset', '')}}/' + id, {_token: '{{ csrf_token() }}'}, function(ret) {
              if (ret.status === 'success') {
                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
              } else {
                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
              }
            });
          }
        });
      }
      @endcan

      @can('admin.user.switch')
      // 切换用户身份
      function switchToUser(id) {
        $.post('{{route('admin.user.switch', '')}}/' + id, {_token: '{{ csrf_token() }}'}, function(ret) {
          if (ret.status === 'success') {
            swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.href = '/');
          } else {
            swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
          }
        });
      }
      @endcan

      @can('admin.user.VNetInfo')
      // 节点连通性测试
      function VNetInfo(id) {
        $.ajax({
          method: 'POST',
          url: '{{route('admin.user.VNetInfo', '')}}/' + id,
          data: {_token: '{{csrf_token()}}'},
          beforeSend: function() {
            $('#vent_' + id).removeClass('wb-link-broken').addClass('wb-loop icon-spin');
          },
          success: function(ret) {
            if (ret.status === 'success') {
              let str = '';
              for (let i in ret.data) {
                str += '<tr><td>' + ret.data[i]['id'] + '</td><td>' + ret.data[i]['name'] + '</td><td>' + ret.data[i]['avaliable'] + '</td></tr>';
              }
              swal.fire({
                title: ret.title,
                icon: 'info',
                html: '<table class="my-20"><thead class="thead-default"><tr><th> ID </th><th> {{ trans('model.node.attribute') }} </th> <th> {{ trans('common.status.attribute')
                 }} </th></thead><tbody>' + str + '</tbody></table>',
                showConfirmButton: false,
              });
            } else {
              swal.fire({title: ret.title, text: ret.data, icon: 'error'});
            }
          },
          complete: function() {
            $('#vent_' + id).removeClass('wb-loop icon-spin').addClass('wb-link-broken');
          },
        });
      }
      @endcan

      const clipboard = new ClipboardJS('.copySubscribeLink');
      clipboard.on('success', function() {
        swal.fire({
          title: '{{ trans('common.copy.success') }}',
          icon: 'success',
          timer: 1000,
          showConfirmButton: false,
        });
      });
      clipboard.on('error', function() {
        swal.fire({
          title: '{{ trans('common.copy.failed') }}',
          icon: 'error',
          timer: 1500,
          showConfirmButton: false,
        });
      });
    </script>
@endsection
