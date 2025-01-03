@extends('user.layouts')
@section('content')
    <div class="page-content">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title cyan-600"><i class="icon wb-help-circle"></i> {{ $ticket->title }} </h1>
                @if ($ticket->status !== 2)
                    <div class="panel-actions">
                        <button class="btn btn-danger" onclick="closeTicket()"> {{ trans('common.close') }} </button>
                    </div>
                @endif
            </div>
            <div class="panel-body">
                <div class="chat-box">
                    <div class="chats">
                        @php
                            $currentUser = Auth::user();
                        @endphp
                        <x-chat-unit :user="$currentUser" :ticket="$ticket" />
                        @foreach ($replyList as $reply)
                            <x-chat-unit :user="$currentUser" :ticket="$reply" />
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="panel-footer pb-30">
                <form>
                    <div class="input-group">
                        <input class="form-control" id="editor" type="text" placeholder="{{ trans('user.ticket.reply_placeholder') }}" />
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="button" onclick="replyTicket()"> {{ trans('common.send') }}</button>
                        </span>
                    </div>
                </form>
            </div>
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
                title: '{{ trans('common.close_item', ['attribute' => trans('user.ticket.attribute')]) }}',
                text: '{{ trans('user.ticket.close_tips') }}',
                icon: 'question',
                showCancelButton: true,
                cancelButtonText: '{{ trans('common.close') }}',
                confirmButtonText: '{{ trans('common.confirm') }}',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        method: 'PATCH',
                        url: '{{ route('ticket.close', $ticket) }}',
                        async: true,
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        dataType: 'json',
                        success: function(ret) {
                            swal.fire({
                                title: ret.message,
                                icon: 'success',
                                timer: 1300,
                            }).then(() => window.location.href = '{{ route('ticket.index') }}');
                        },
                        error: function() {
                            swal.fire({
                                title: '{{ trans('user.ticket.error') }}',
                                icon: 'error'
                            });
                        },
                    });
                }
            });
        }

        // 回复工单
        function replyTicket() {
            const content = document.getElementById('editor').value;

            if (content.trim() === '') {
                swal.fire({
                    title: '{{ ucfirst(trans('validation.required', ['attribute' => trans('validation.attributes.content')])) }}!',
                    icon: 'warning',
                    timer: 1500,
                });
                return false;
            }
            swal.fire({
                title: '{{ trans('user.ticket.reply_confirm') }}',
                icon: 'question',
                allowEnterKey: false,
                showCancelButton: true,
                cancelButtonText: '{{ trans('common.close') }}',
                confirmButtonText: '{{ trans('common.confirm') }}',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        method: 'PUT',
                        url: '{{ route('ticket.reply', $ticket) }}',
                        async: true,
                        data: {
                            _token: '{{ csrf_token() }}',
                            content: content,
                        },
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
                                swal.fire({
                                    title: ret.message,
                                    icon: 'error'
                                }).then(() => window.location.reload());
                            }
                        },
                        error: function() {
                            swal.fire({
                                title: '{{ trans('user.ticket.error') }}',
                                icon: 'error'
                            });
                        },
                    });
                }
            });
        }
    </script>
@endsection
