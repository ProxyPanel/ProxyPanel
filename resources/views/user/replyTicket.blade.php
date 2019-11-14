@extends('user.layouts')
@section('css')
    <link href="/assets/examples/css/structure/chat.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title cyan-600"><i class="icon wb-help-circle"></i>{{$ticket->title}}</h1>
                @if($ticket->status != 2)
                    <div class="panel-actions">
                        <button class="btn btn-danger" onclick="closeTicket()"> {{trans('home.ticket_close')}} </button>
                    </div>
                @endif
            </div>
            <div class="panel-body">
                <div class="chat-box">
                    <div class="chats">
                        <div class="chat">
                            <div class="chat-avatar">
                                <p class="avatar" data-toggle="tooltip" data-placement="right" title="{{trans('home.ticket_reply_me')}}">
                                    <img src="/assets/images/astronaut.svg" alt="{{trans('home.ticket_reply_me')}}"/>
                                </p>
                            </div>
                            <div class="chat-body">
                                <div class="chat-content">
                                    <p>
                                        {!! $ticket->content !!}
                                    </p>
                                    <time class="chat-time">{{$ticket->created_at}}</time>
                                </div>
                            </div>
                        </div>
                        @foreach ($replyList as $reply)
                            <div class="chat @if($reply->user->is_admin) chat-left @endif">
                                <div class="chat-avatar">
                                    @if ($reply->user->is_admin)
                                        <p class="avatar" data-toggle="tooltip" data-placement="left" title="{{trans('home.ticket_reply_master')}}">
                                            <img src="/assets/images/logo64.png" alt="{{trans('home.ticket_reply_master')}}"/>
                                        </p>
                                    @else
                                        <p class="avatar" data-toggle="tooltip" data-placement="left" title="{{trans('home.ticket_reply_me')}}">
                                            <img src="/assets/images/astronaut.svg" alt="{{trans('home.ticket_reply_me')}}"/>
                                        </p>
                                    @endif
                                </div>
                                <div class="chat-body">
                                    <div class="chat-content">
                                        <p>
                                            {!! $reply->content!!}
                                        </p>
                                        <time class="chat-time">{{$reply->created_at}}</time>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @if($ticket->status != 2)
                <div class="panel-footer pb-30">
                    <form">
                        <div class="input-group">
                            <input id="editor" type="text" class="form-control" placeholder="{{trans('home.ticket_reply_placeholder')}}"/>
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
@section('script')
    <script type="text/javascript">
        //回车检测
        $(document).on("keypress", "input", function(e){
            if(e.which === 13){replyTicket()}
        });
        // 关闭工单
        function closeTicket() {
            swal.fire({
                title: '{{trans('home.ticket_close_title')}}',
                text: '{{trans('home.ticket_close_content')}}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        type: "POST",
                        url: "/closeTicket",
                        async: true,
                        data: {_token: '{{csrf_token()}}', id: '{{$ticket->id}}'},
                        dataType: 'json',
                        success: function (ret) {
                            swal.fire({
                                title: ret.message,
                                type: 'success',
                                timer: 1300
                            }).then(() => window.location.href = '/tickets')
                        },
                        error: function () {
                            swal.fire("未知错误！请通知客服！")
                        }
                    });
                }
            })
        }

        // 回复工单
        function replyTicket() {
            const content = document.getElementById('editor').value;

            if (content.trim() === '') {
                swal.fire({title: '您未填写工单内容!', type: 'warning'});
                return false;
            }
            swal.fire({
                title: '确定回复工单？',
                type: 'question',
                allowEnterKey: false,
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/replyTicket", {
                        _token: '{{csrf_token()}}',
                        id: '{{$ticket->id}}',
                        content: content
                    }, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000})
                                .then(() => window.location.reload())
                        } else {
                            swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                        }
                    });
                }
            })
        }
    </script>
@endsection
