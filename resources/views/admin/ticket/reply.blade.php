@extends('admin.layouts')
@section('content')
    <div class="page-content">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title cyan-600"><i class="icon wb-help-circle"></i> {{$ticket->title}} </h1>
                <div class="panel-actions">
                    <a href="{{route('admin.ticket.index')}}" class="btn btn-default">返 回</a>
                    @if($ticket->status !== 2)
                        @can('admin.ticket.destroy')
                            <button class="btn btn-danger" onclick="closeTicket()"> {{trans('home.ticket_close')}} </button>
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
            @if($ticket->status !== 2)
                @can('admin.ticket.update')
                    <div class="panel-footer pb-30">
                        <form>
                            <div class="input-group">
                                <input type="text" class="form-control" id="editor" placeholder="{{trans('home.ticket_reply_placeholder')}}"/>
                                <span class="input-group-btn">
                                <button type="button" class="btn btn-primary" onclick="replyTicket()"> {{trans('home.ticket_reply')}}</button>
                            </span>
                            </div>
                        </form>
                    </div>
                @endcan
            @endif
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
            cancelButtonText: '{{trans('home.ticket_close')}}',
            confirmButtonText: '{{trans('home.ticket_confirm')}}',
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
                  swal.fire({title: '未知错误！请通知客服', icon: 'error'});
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
            swal.fire({title: '您未填写工单内容!', icon: 'warning', timer: 1500});
            return false;
          }
          swal.fire({
            title: '确定回复工单？',
            icon: 'question',
            allowEnterKey: false,
            showCancelButton: true,
            cancelButtonText: '{{trans('home.ticket_close')}}',
            confirmButtonText: '{{trans('home.ticket_confirm')}}',
          }).then((result) => {
            if (result.value) {
              $.ajax({
                method: 'PUT',
                url: '{{route('admin.ticket.update', $ticket->id)}}',
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
