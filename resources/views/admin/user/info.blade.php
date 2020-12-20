@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">@isset($user) 编辑用户 @else 添加用户 @endisset</h2>
                @isset($user)
                    @can('admin.user.switch')
                        <div class="panel-actions">
                            <button type="button" class="btn btn-sm btn-danger" onclick="switchToUser()">切换身份</button>
                        </div>
                    @endcan
                @endisset
            </div>
            <div class="panel-body">
                <form class="form-horizontal" onsubmit="return Submit()">
                    <div class="form-row">
                        <div class="col-lg-6">
                            <h4 class="example-title">账号信息</h4>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="username">昵称</label>
                                <div class="col-xl-6 col-sm-8">
                                    <input type="text" class="form-control" name="username" id="username" required/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="email">邮箱</label>
                                <div class="col-xl-6 col-sm-8">
                                    <input type="email" class="form-control" name="email" id="email" required/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="password">密码</label>
                                <div class="col-xl-6 col-sm-8">
                                    <input type="password" class="form-control" name="password" id="password" autocomplete="new-password"
                                           placeholder="@isset($user)留空则自动生成随机密码 @else 不填则不变 @endisset"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="level">级别</label>
                                <div class="col-xl-4 col-sm-8">
                                    <select class="form-control" name="level" id="level" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                        @foreach($levels as $level)
                                            <option value="{{$level->level}}">{{$level->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="group">分组</label>
                                <div class="col-xl-4 col-sm-8">
                                    <select class="form-control" name="group" id="group" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                        <option value="0">无分组</option>
                                        @foreach($userGroups as $group)
                                            <option value="{{$group->id}}">{{$group->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @isset($user)
                                <div class="form-group row">
                                    <label class="col-md-2 col-sm-3 col-form-label" for="credit">余额</label>
                                    <div class="col-xl-4 col-sm-8">
                                        <div class="input-group">
                                            <p class="form-control"> {{$user->credit}} </p>
                                            @can('admin.user.updateCredit')
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#handle_user_credit">充值</button>
                                                </div>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endisset

                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="invite_num">可用邀请码</label>
                                <div class="col-xl-4 col-sm-8">
                                    <input type="number" class="form-control" name="invite_num" id="invite_num" value="0" required/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="reset_time">重置日</label>
                                <div class="col-xl-4 col-sm-4">
                                    <div class="input-group input-daterange" data-plugin="datepicker">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="icon wb-calendar" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" name="reset_time" id="reset_time"/>
                                    </div>
                                    <span class="text-help"> 账号流量下一个重置日期 </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="expired_at">过期日</label>
                                <div class="col-xl-4 col-sm-4">
                                    <div class="input-group input-daterange" data-plugin="datepicker">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="icon wb-calendar" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" name="expired_at" id="expired_at"/>
                                    </div>
                                    <span class="text-help"> 留空默认为一年 </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label">账户状态</label>
                                <div class="col-md-10 col-sm-8">
                                    <ul class="list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="status" id="normal" value="1" checked/>
                                                <label for="normal">正常</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="status" id="nonactive" value="0"/>
                                                <label for="nonactive">未激活</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="status" id="baned" value="-1"/>
                                                <label for="baned">禁用</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="roles">角色权限</label>
                                <div class="col-xl-4 col-sm-8">
                                    <select class="form-control show-tick" name="roles[]" id="roles" data-plugin="selectpicker" data-style="btn-outline btn-primary" multiple>
                                        @foreach($roles as $key => $description)
                                            <option value="{{ $key }}">{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="wechat">微信</label>
                                <div class="col-xl-6 col-sm-8">
                                    <input type="text" class="form-control" name="wechat" id="wechat"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="qq">QQ</label>
                                <div class="col-xl-6 col-sm-8">
                                    <input type="number" class="form-control" name="qq" id="qq"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="remark">备注</label>
                                <div class="col-xl-6 col-sm-8">
                                    <textarea class="form-control" rows="3" name="remark" id="remark"></textarea>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="inviter">邀请人</label>
                                <div class="col-xl-6 col-sm-8">
                                    <p class="form-control"> {{empty($user->inviter) ? '无邀请人' : $user->inviter->email}} </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h4 class="example-title">代理信息</h4>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="port">端口</label>
                                <div class="col-xl-5 col-sm-8">
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="port" id="port" placeholder="留空则自动生成随机端口"/>
                                        <div class="input-group-append">
                                            <button class="btn btn-success" type="button" onclick="makePort()">
                                                <i class="icon wb-refresh"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="uuid">VMess UUID</label>
                                <div class="col-xl-5 col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="uuid" id="uuid" placeholder="留空则自动生成随机UUID"/>
                                        <div class="input-group-append">
                                            <button class="btn btn-success" type="button" onclick="makeUUID()">
                                                <i class="icon wb-refresh"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <span class="text-help"> V2Ray的账户ID </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="passwd">密码</label>
                                <div class="col-xl-5 col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="passwd" id="passwd" placeholder="留空则自动生成随机密码"/>
                                        <div class="input-group-append">
                                            <button class="btn btn-success" type="button" onclick="makePasswd()">
                                                <i class="icon wb-refresh"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="method">加密方式</label>
                                <div class="col-xl-5 col-sm-8">
                                    <select class="form-control" name="method" id="method" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                        @foreach (Helpers::methodList() as $method)
                                            <option value="{{$method->name}}" @if($method->is_default) selected @endif>{{$method->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="transfer_enable">可用流量</label>
                                <div class="col-xl-5 col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="transfer_enable" id="transfer_enable" value="1024" required>
                                        <span class="input-group-text">GB</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label">代理状态</label>
                                <div class="col-md-10 col-sm-8">
                                    <ul class="list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="enable" id="enable" value="1" checked/>
                                                <label for="enable">启用</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="enable" id="disable" value="0"/>
                                                <label for="disable">禁用</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="protocol">协议</label>
                                <div class="col-xl-5 col-sm-8">
                                    <select class="form-control" name="protocol" id="protocol" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                        @foreach (Helpers::protocolList() as $protocol)
                                            <option value="{{$protocol->name}}" @if($protocol->is_default) selected @endif>{{$protocol->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="obfs">混淆</label>
                                <div class="col-xl-5 col-sm-8">
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control" name="obfs" id="obfs">
                                        @foreach (Helpers::obfsList() as $obfs)
                                            <option value="{{$obfs->name}}" @if($obfs->is_default) selected @endif>{{$obfs->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="speed_limit">用户限速</label>
                                <div class="col-xl-5 col-sm-8">
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="speed_limit" id="speed_limit" value="200"/>
                                        <span class="input-group-text"> Mbps</span>
                                    </div>
                                    <span class="text-help">为 0 时不限速 </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 form-actions text-right">
                            <a href="{{route('admin.user.index')}}" class="btn btn-secondary">返 回</a>
                            <button type="submit" class="btn btn-success">提 交</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @isset($user)
        @can('admin.user.updateCredit')
            <!-- 余额充值 -->
            <div class="modal fade" id="handle_user_credit" aria-hidden="true" role="dialog" tabindex="-1">
                <div class="modal-dialog modal-simple modal-center">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title">充值</h4>
                        </div>
                        <form action="#" method="post" class="modal-body">
                            <div class="alert alert-danger" style="display: none;" id="msg"></div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="amount"> 充值金额 </label>
                                <input type="number" class="col-sm-4 form-control" name="amount" id="amount" placeholder="填入负值则会扣余额" step="0.01"
                                       onkeydown="if(event.keyCode===13){return false;}"/>
                            </div>
                        </form>
                        <div class="modal-footer">
                            <button data-dismiss="modal" class="btn btn-danger">关闭</button>
                            <button type="button" class="btn btn-primary" onclick="handleUserCredit()">充值</button>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    @endisset
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
      $(document).ready(function() {
          @isset($user)
          $('#username').val('{{$user->username}}');
        $('#email').val('{{$user->email}}');
        $('#level').selectpicker('val', '{{$user->level}}');
        $('#group').selectpicker('val', '{{$user->group_id}}');
        $('#invite_num').val('{{$user->invite_num}}');
        $('#reset_time').val('{{$user->reset_time}}');
        $('#expired_at').val('{{$user->expired_at}}');
        $("input[name='status'][value='{{$user->status}}']").click();
        $('#wechat').val('{{$user->wechat}}');
        $('#qq').val('{{$user->qq}}');
        $('#remark').val('{{$user->remark}}');
        $('#port').val('{{$user->port}}');
        $('#passwd').val('{{$user->passwd}}');
        $('#method').selectpicker('val', '{{$user->method}}');
        $('#transfer_enable').val('{{$user->transfer_enable/GB}}');
        $("input[name='enable'][value='{{$user->enable}}']").click();
        $('#protocol').selectpicker('val', '{{$user->protocol}}');
        $('#obfs').selectpicker('val', '{{$user->obfs}}');
        $('#speed_limit').val('{{$user->speed_limit}}');
        $('#uuid').val('{{$user->vmess_id}}');
        $('#roles').selectpicker('val', @json($user->roles()->pluck('name')));
          @else
          $('#level').selectpicker('val', '0');
          @endisset
      });

      $('.input-daterange>input').datepicker({
        format: 'yyyy-mm-dd',
      });

      @isset($user)
      @can('admin.user.switch')
      // 切换用户身份
      function switchToUser() {
        $.ajax({
          url: '{{route('admin.user.switch')}}',
          data: {
            'user_id': '{{$user->id}}',
            '_token': '{{csrf_token()}}',
          },
          dataType: 'json',
          method: 'POST',
          success: function(ret) {
            if (ret.status === 'success') {
              swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.href = '/');
            } else {
              swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
            }
          },
        });
      }
      @endcan

      @can('admin.user.updateCredit')
      // 余额充值
      function handleUserCredit() {
        const amount = $('#amount').val();
        const reg = /^(-?)\d+(\.\d+)?$/; //只可以是正负数字

        if (amount.trim() === '' || amount === 0 || !reg.test(amount)) {
          $('#msg').show().html('请输入充值金额');
          $('#name').focus();
          return false;
        }

        $.ajax({
          url: '{{route('admin.user.updateCredit')}}',
          method: 'POST',
          data: {_token: '{{csrf_token()}}', user_id: '{{$user->id}}', amount: amount},
          beforeSend: function() {
            $('#msg').show().html('充值中...');
          },
          success: function(ret) {
            if (ret.status === 'fail') {
              $('#msg').show().html(ret.message);
              return false;
            } else {
              $('#handle_user_credit').modal('hide');
              if (ret.status === 'success') {
                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => {
                  window.location.reload();
                });
              } else {
                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
              }
            }
          },
          error: function() {
            $('#msg').show().html('请求错误，请重试');
          },
          complete: function() {
          },
        });
      }
      @endcan
      @endisset

      // ajax同步提交
      function Submit() {
        // 用途
        let usage = '';
        $.each($('input:checkbox[name=\'usage\']'), function() {
          if (this.checked) {
            usage += $(this).val() + ',';
          }
        });

        $.ajax({
          method: @isset($user)'PUT' @else 'POST' @endisset,
          url: '{{isset($user)? route('admin.user.update', $user->id) : route('admin.user.store')}}',
          async: false,
          data: {
            _token: '{{csrf_token()}}',
            username: $('#username').val(),
            email: $('#email').val(),
            password: $('#password').val(),
            port: $('#port').val(),
            passwd: $('#passwd').val(),
            uuid: $('#uuid').val(),
            transfer_enable: $('#transfer_enable').val(),
            enable: $('input:radio[name=\'enable\']:checked').val(),
            method: $('#method option:selected').val(),
            protocol: $('#protocol option:selected').val(),
            obfs: $('#obfs option:selected').val(),
            speed_limit: $('#speed_limit').val(),
            wechat: $('#wechat').val(),
            qq: $('#qq').val(),
            expired_at: $('#expired_at').val(),
            remark: $('#remark').val(),
            level: $('#level').val(),
            group_id: $('#group').val(),
            roles: $('#roles').val(),
            reset_time: $('#reset_time').val(),
            invite_num: $('#invite_num').val(),
            status: $('input:radio[name=\'status\']:checked').val(),
          },
          dataType: 'json',
          success: function(ret) {
            if (ret.status === 'success') {
              swal.fire({
                title: '提示',
                text: '更新成功，是否返回？',
                icon: 'question',
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
              }).then((result) => {
                    if (result.value) {
                      window.location.href = '{!! route('admin.user.index').(Request::getQueryString()?('?'.Request::getQueryString()):'') !!}';
                    }
                  },
              );
            } else {
              swal.fire({title: ret.message, icon: 'error', timer: 1000, showConfirmButton: false});
            }
          },
          error: function(data) {
            let str = '';
            const errors = data.responseJSON;
            if ($.isEmptyObject(errors) === false) {
              $.each(errors.errors, function(index, value) {
                str += '<li>' + value + '</li>';
              });
              swal.fire({title: '提示', html: str, icon: 'error', confirmButtonText: '{{trans('home.ticket_confirm')}}'});
            }
          },
        });

        return false;
      }

      // 生成随机端口
      function makePort() {
        $.get('{{route('getPort')}}', function(ret) {
          $('#port').val(ret);
        });
      }

      // 生成UUID
      function makeUUID() {
        $.get('{{route('createUUID')}}', function(ret) {
          $('#uuid').val(ret);
        });
      }

      // 生成随机密码
      function makePasswd() {
        $.get('{{route('createStr')}}', function(ret) {
          $('#passwd').val(ret);
        });
      }
    </script>
@endsection
