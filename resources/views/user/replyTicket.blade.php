@extends('user.layouts')
@section('content')
    <div class="page-content">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title cyan-600"><i class="icon wb-help-circle"></i> {{$ticket->title}} </h1>
                @if($ticket->status !== 2)
                    <div class="panel-actions">
                        <button class="btn btn-danger" onclick="closeTicket()"> {{trans('home.ticket_close')}} </button>
                    </div>
                @endif
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
            @endif
        </div>
    </div>
@endsection
@section('javascript')
    <script>
      //回车检测
      $(document).on('keypress', 'input', function(e) {
        if (e.which === 13) {
          replyTicket();
          return false;
        }
      });

      // 关闭工单
      function closeTicket() {
        swal.fire({
          title: '{{trans('home.ticket_close_title')}}',
          text: '{{trans('home.ticket_close_content')}}',
          icon: 'question',
          showCancelButton: true,
          cancelButtonText: '{{trans('home.ticket_close')}}',
          confirmButtonText: '{{trans('home.ticket_confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'POST',
              url: '{{route('closeTicket')}}',
              async: true,
              data: {_token: '{{csrf_token()}}', id: '{{$ticket->id}}'},
              dataType: 'json',
              success: function(ret) {
                swal.fire({
                  title: ret.message,
                  icon: 'success',
                  timer: 1300,
                }).then(() => window.location.href = '{{route('ticket')}}');
              },
              error: function() {
                swal.fire({title: '未知错误！请通知客服', icon: 'error'});
              },
            });
          }
        });
      }

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
            $.post('{{route('replyTicket')}}', {
              _token: '{{csrf_token()}}',
              id: '{{$ticket->id}}',
              content: content,
            }, function(ret) {
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
    </script>
@endsection
