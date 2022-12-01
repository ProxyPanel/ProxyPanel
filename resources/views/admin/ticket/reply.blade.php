@extends('admin.layouts')
@section('content')
    <div class="page-content">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title cyan-600">
                    <i class="icon wb-help-circle"></i> {{$ticket->title}}
                </h1>
                <div class="panel-actions btn-group">
                    <button class="btn icon-1x btn-info btn-icon wb-user-circle" data-target="#userInfo" data-toggle="modal" type="button"> 用户信息</button>
                    <a href="{{route('admin.ticket.index')}}" class="btn btn-default">返 回</a>
                    @if($ticket->status !== 2)
                        @can('admin.ticket.destroy')
                            <button class="btn btn-danger" onclick="closeTicket()"> {{trans('common.close')}} </button>
                        @endcan
                    @endif
                </div>
            </div>
            <div class="panel-body">
                <div class="chat-box">
                    <div class="chats">
                        <x-chat-unit :user="Auth::getUser()" :ticket="$ticket"/>
                        @foreach ($replyList as $reply)
                            <x-chat-unit :user="Auth::getUser()" :ticket="$reply"/>
                        @endforeach
                    </div>
                </div>
            </div>
            @can('admin.ticket.update')
                <div class="panel-footer pb-30">
                    <form>
                        <div class="input-group">
                            <input type="text" class="form-control" id="editor" placeholder="{{trans('user.ticket.reply_placeholder')}}"/>
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-primary" onclick="replyTicket()"> {{trans('common.send')}}</button>
                            </span>
                        </div>
                    </form>
                </div>
            @endcan
        </div>
    </div>
    <div class="modal fade" id="userInfo" aria-hidden="true" aria-labelledby="userInfo"
         role="dialog" tabindex="-1">
        <div class="modal-dialog modal-simple modal-sidebar">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="wb-user" aria-hidden="true"></i> 用户信息</h4>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-dividered px-20 mb-0">
                        <h5>基础信息</h5>
                        <dl class="dl-horizontal row">
                            <dt class="col-sm-3">昵称</dt>
                            <dd class="col-sm-9">{{$user->nickname}}</dd>
                            <dt class="col-sm-3">账号</dt>
                            <dd class="col-sm-9">{{$user->username}}</dd>
                            <dt class="col-sm-3">账号状态</dt>
                            <dd class="col-sm-9">
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
                            </dd>
                            <dt class="col-sm-3">等级</dt>
                            <dd class="col-sm-9">{{$user->level}}</dd>
                            <dt class="col-sm-3">分组</dt>
                            <dd class="col-sm-9">{{$user->userGroup->name ?? '无分组'}}</dd>
                            <dt class="col-sm-3">余额</dt>
                            <dd class="col-sm-9">{{$user->credit}}</dd>
                            <dt class="col-sm-3">流量</dt>
                            <dd class="col-sm-9">{{flowAutoShow($user->used_traffic)}} / {{$user->transfer_enable_formatted}}</dd>
                            <dt class="col-sm-3">重置日期</dt>
                            <dd class="col-sm-9">{{$user->reset_date ?? '无'}}</dd>
                            <dt class="col-sm-3">最近使用</dt>
                            <dd class="col-sm-9">
                                {{$user->t? date('Y-m-d H:i', $user->t): '未使用'}}
                            </dd>
                            <dt class="col-sm-3">过期日期</dt>
                            <dd class="col-sm-9">
                                @if($user->expiration_status() !== 3)
                                    <span class="badge badge-lg badge-{{['danger','warning','default'][$user->expiration_status()]}}"> {{ $user->expiration_date }} </span>
                                @else
                                    {{ $user->expiration_date }}
                                @endif
                            </dd>
                            <dt class="col-sm-3">备注</dt>
                            <dt class="col-sm-3">{!! $user->remark !!}</dt>
                        </dl>
                        <h5>代理信息</h5>
                        <dl class="dl-horizontal row">
                            <dt class="col-sm-3">开启状态</dt>
                            <dd class="col-sm-9">
                                <span class="badge badge-lg badge-{{$user->enable?'info':'danger'}}">
                                    <i class="wb-{{$user->enable?'check':'close'}}" aria-hidden="true"></i>
                                </span>
                            </dd>
                            <dt class="col-sm-3">端口</dt>
                            <dd class="col-sm-9">{!!$user->port? : '<span class="badge badge-lg badge-danger"> 未分配 </span>'!!}</dd>
                        </dl>
                        <h5>其他</h5>
                        <dl class="dl-horizontal row">
                            <dt class="col-sm-3">邀请人信息</dt>
                            <dd class="col-sm-9">
                                {{$user->inviter->nickname ?? '无'}}
                            </dd>
                            @isset ($user->inviter)
                                <dt class="col-sm-3 offset-md-1">账号</dt>
                                <dd class="col-sm-8">{{$user->inviter->username}}</dd>
                                <dt class="col-sm-3 offset-md-1">等级</dt>
                                <dd class="col-sm-8">{{$user->inviter->level}}</dd>
                                <dt class="col-sm-3 offset-md-1">最近使用</dt>
                                <dd class="col-sm-8">
                                    {{$user->inviter->t? date('Y-m-d H:i', $user->inviter->t): '未使用'}}
                                </dd>
                            @endif
                        </dl>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('common.close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
        @can('admin.ticket.destroy')
        // 关闭工单
        function closeTicket() {
          swal.fire({
            title: '确定关闭工单？',
            icon: 'question',
            showCancelButton: true,
            cancelButtonText: '{{trans('common.close')}}',
            confirmButtonText: '{{trans('common.confirm')}}',
          }).then((result) => {
            if (result.value) {
              $.ajax({
                method: 'DELETE',
                url: '{{route('admin.ticket.destroy', $ticket->id)}}',
                async: true,
                data: {_token: '{{csrf_token()}}'},
                dataType: 'json',
                success: function(ret) {
                  if (ret.status === 'success') {
                    swal.fire({
                      title: ret.message,
                      icon: 'success',
                      timer: 1000,
                      showConfirmButton: false,
                    }).then(() => window.location.href = '{{route('admin.ticket.index')}}');
                  } else {
                    swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                  }
                },
                error: function() {
                  swal.fire({title: '{{trans('user.ticket.error')}}', icon: 'error'});
                },
              });
            }
          });
        }
        @endcan

        @can('admin.ticket.update')
        //回车检测
        $(document).on('keypress', 'input', function(e) {
          if (e.which === 13) {
            replyTicket();
            return false;
          }
        });

        // 回复工单
        function replyTicket() {
          const content = document.getElementById('editor').value;

          if (content.trim() === '') {
            swal.fire({title: '{{trans('validation.required', ['attribute' => trans('validation.attributes.content')])}}!', icon: 'warning', timer: 1500});
            return false;
          }
          swal.fire({
            title: '{{trans('user.ticket.reply_confirm')}}',
            icon: 'question',
            allowEnterKey: false,
            showCancelButton: true,
            cancelButtonText: '{{trans('common.close')}}',
            confirmButtonText: '{{trans('common.confirm')}}',
          }).then((result) => {
            if (result.value) {
              $.ajax({
                method: 'PUT',
                url: '{{route('admin.ticket.update', $ticket)}}',
                data: {_token: '{{csrf_token()}}', content: content},
                dataType: 'json',
                success: function(ret) {
                  if (ret.status === 'success') {
                    swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                  } else {
                    swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                  }
                },
                error: function() {
                  swal.fire({title: '未知错误！请查看运行日志', icon: 'error'});
                },
              });
            }
          });
        }
        @endcan
    </script>
@endsection
