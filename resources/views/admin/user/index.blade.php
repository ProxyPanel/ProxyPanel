@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/custom/range.min.css" rel="stylesheet">
    <style>
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
                <h2 class="panel-title">用户列表</h2>
                @canany(['admin.user.batch', 'admin.user.create'])
                    <div class="panel-actions">
                        @can('admin.user.batch')
                            <button class="btn btn-outline-default" onclick="batchAddUsers()">
                                <i class="icon wb-plus" aria-hidden="true"></i>批量生成
                            </button>
                        @endcan
                        @can('admin.user.create')
                            <a href="{{route('admin.user.create')}}" class="btn btn-outline-default">
                                <i class="icon wb-user-add" aria-hidden="true"></i>添加用户
                            </a>
                        @endcan
                    </div>
                @endcanany
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-xxl-1 col-lg-1 col-md-1 col-sm-4">
                        <input type="number" class="form-control" id="id" name="id" value="{{Request::input('id')}}" placeholder="ID"/>
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <input type="text" class="form-control" id="email" name="email" value="{{Request::input('email')}}" placeholder="用户名"/>
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <input type="text" class="form-control" id="wechat" name="wechat" value="{{Request::input('wechat')}}" placeholder="微信"/>
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <input type="number" class="form-control" id="qq" name="qq" value="{{Request::input('qq')}}" placeholder="QQ"/>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-2 col-md-2 col-sm-4">
                        <input type="number" class="form-control" id="port" name="port" value="{{Request::input('port')}}" placeholder="端口"/>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="group" name="group" onChange="Search()">
                            <option value="" hidden>用户分组</option>
                            @foreach($userGroups as $key => $group)
                                <option value="{{$key}}">{{$group}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="level" name="level" onChange="Search()">
                            <option value="" hidden>用户分组</option>
                            @foreach($levels as $key => $level)
                                <option value="{{$key}}">{{$level}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="status" name="status" onChange="Search()">
                            <option value="" hidden>账号状态</option>
                            <option value="-1">禁用</option>
                            <option value="0">未激活</option>
                            <option value="1">正常</option>
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="enable" name="enable" onChange="Search()">
                            <option value="" hidden>代理状态</option>
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.user.index')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户名</th>
                        <th> 余额</th>
                        <th> 端口</th>
                        <th> 订阅码</th>
                        <th> 流量使用</th>
                        <th> 最后使用</th>
                        <th> 有效期</th>
                        <th> 状态</th>
                        <th> 代理</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($userList as $user)
                        <tr class="{{$user->isTrafficWarning() ? ' table-danger' : ''}}">
                            <td> {{$user->id}} </td>
                            <td> {{$user->email}} </td>
                            <td> {{$user->credit}} </td>
                            <td>
                                {!!$user->port? : '<span class="badge badge-lg badge-danger"> 未分配 </span>'!!}
                            </td>
                            <td>
                                <a href="javascript:" class="copySubscribeLink" data-clipboard-action="copy"
                                   data-clipboard-text="{{$user->subUrl()}}">{{$user->subscribe->code}}</a>
                            </td>
                            <td> {{flowAutoShow($user->usedTraffic())}} / {{$user->transfer_enable_formatted}} </td>
                            <td> {{$user->t? date('Y-m-d H:i', $user->t): '未使用'}} </td>

                            <td>
                                @if ($user->expired_at < date('Y-m-d'))
                                    <span class="badge badge-lg badge-danger"> {{$user->expired_at}} </span>
                                @elseif ($user->expired_at === date('Y-m-d'))
                                    <span class="badge badge-lg badge-warning"> {{$user->expired_at}} </span>
                                @elseif ($user->expired_at <= date('Y-m-d', strtotime('+30 days')))
                                    <span class="badge badge-lg badge-default"> {{$user->expired_at}} </span>
                                @else
                                    {{$user->expired_at}}
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
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-primary dropdown-toggle" data-boundary="viewport" data-toggle="dropdown" aria-expanded="false">
                                            <i class="icon wb-wrench" aria-hidden="true"></i>
                                        </button>
                                        <div class="dropdown-menu" role="menu">
                                            @can('admin.user.edit')
                                                <a class="dropdown-item" href="{{route('admin.user.edit', ['user'=>$user->id, Request::getQueryString()])}}" role="menuitem">
                                                    <i class="icon wb-edit" aria-hidden="true"></i> 编辑
                                                </a>
                                            @endcan
                                            @can('admin.user.destroy')
                                                <a class="dropdown-item" href="javascript:delUser('{{route('admin.user.destroy', $user->id)}}','{{$user->email}}')" role="menuitem">
                                                    <i class="icon wb-trash" aria-hidden="true"></i> 删除
                                                </a>
                                            @endcan
                                            @can('admin.user.export')
                                                <a class="dropdown-item" href="{{route('admin.user.export', $user)}}" role="menuitem">
                                                    <i class="icon wb-code" aria-hidden="true"></i> 配置信息
                                                </a>
                                            @endcan
                                            @can('admin.user.monitor')
                                                <a class="dropdown-item" href="{{route('admin.user.monitor', $user)}}" role="menuitem">
                                                    <i class="icon wb-stats-bars" aria-hidden="true"></i> 流量统计
                                                </a>
                                            @endcan
                                            @can('admin.user.online')
                                                <a class="dropdown-item" href="{{route('admin.user.online', $user)}}" role="menuitem">
                                                    <i class="icon wb-cloud" aria-hidden="true"></i> 在线巡查
                                                </a>
                                            @endcan
                                            @can('admin.user.reset')
                                                <a class="dropdown-item" href="javascript:resetTraffic('{{$user->id}}','{{$user->email}}')" role="menuitem">
                                                    <i class="icon wb-reload" aria-hidden="true"></i> 重置流量
                                                </a>
                                            @endcan
                                            @can('admin.user.switch')
                                                <a class="dropdown-item" href="javascript:switchToUser('{{$user->id}}')" role="menuitem">
                                                    <i class="icon wb-user" aria-hidden="true"></i> 用户视角
                                                </a>
                                            @endcan
                                        </div>
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
                        共 <code>{{$userList->total()}}</code> 个账号
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
        $('#group').val({{Request::input('group')}});
        $('#level').val({{Request::input('level')}});
        $('#pay_way').val({{Request::input('pay_way')}});
        $('#status').val({{Request::input('status')}});
        $('#enable').val({{Request::input('enable')}});
      });

      //回车检测
      $(document).on('keypress', 'input', function(e) {
        if (e.which === 13) {
          Search();
          return false;
        }
      });

      // 搜索
      function Search() {
        window.location.href = '{{route('admin.user.index')}}' + '?id=' + $('#id').val() + '&email=' + $('#email').val() + '&wechat=' +
            $('#wechat').val() + '&qq=' + $('#qq').val() + '&port=' + $('#port').val() + '&group=' + $('#group option:selected').val() + '&level='
            + $('#level option:selected').val() + '&status=' + $('#status option:selected').val() + '&enable=' + $('#enable option:selected').val();
      }

      @can('admin.user.batch')
      // 批量生成账号
      function batchAddUsers() {
        swal.fire({
          title: '用户生成数量',
          input: 'range',
          inputAttributes: {min: 1, max: 10},
          inputValue: 1,
          icon: 'question',
          showCancelButton: true,
          cancelButtonText: '{{trans('home.ticket_close')}}',
          confirmButtonText: '{{trans('home.ticket_confirm')}}',
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
      function delUser(url, email) {
        swal.fire({
          title: '警告',
          text: '确定删除用户 【' + email + '】 ？',
          icon: 'warning',
          showCancelButton: true,
          cancelButtonText: '{{trans('home.ticket_close')}}',
          confirmButtonText: '{{trans('home.ticket_confirm')}}',
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
      function resetTraffic(id, email) {
        swal.fire({
          title: '警告',
          text: '确定重置 【' + email + '】 流量吗？',
          icon: 'warning',
          showCancelButton: true,
          cancelButtonText: '{{trans('home.ticket_close')}}',
          confirmButtonText: '{{trans('home.ticket_confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.post('{{route('admin.user.reset')}}', {_token: '{{csrf_token()}}', id: id}, function(ret) {
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
        $.post('{{route('admin.user.switch')}}', {_token: '{{csrf_token()}}', user_id: id}, function(ret) {
          if (ret.status === 'success') {
            swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.href = '/');
          } else {
            swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
          }
        });
      }
      @endcan

      const clipboard = new ClipboardJS('.copySubscribeLink');
      clipboard.on('success', function() {
        swal.fire({
          title: '复制成功',
          icon: 'success',
          timer: 1000,
          showConfirmButton: false,
        });
      });
      clipboard.on('error', function() {
        swal.fire({
          title: '复制失败，请手动复制',
          icon: 'error',
          timer: 1500,
          showConfirmButton: false,
        });
      });
    </script>
@endsection
