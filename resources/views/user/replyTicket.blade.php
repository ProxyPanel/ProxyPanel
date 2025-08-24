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
            showConfirm({
                title: '{{ trans('common.close_item', ['attribute' => trans('user.ticket.attribute')]) }}',
                text: '{{ trans('user.ticket.close_tips') }}',
                onConfirm: function() {
                    ajaxPatch('{{ route('ticket.close', $ticket) }}', {}, {
                        success: function(ret) {
                            handleResponse(ret, {
                                redirectUrl: '{{ route('ticket.index') }}'
                            })
                        }
                    });
                }
            });
        }

        // 回复工单
        function replyTicket() {
            const content = document.getElementById('editor').value;

            if (content.trim() === '') {
                showMessage({
                    title: '{{ ucfirst(trans('validation.required', ['attribute' => trans('validation.attributes.content')])) }}!',
                    icon: 'warning',
                    showConfirmButton: true
                });
                return false;
            }

            showConfirm({
                title: '{{ trans('user.ticket.reply_confirm') }}',
                onConfirm: function() {
                    ajaxPut('{{ route('ticket.reply', $ticket) }}', {
                        content: content
                    });
                }
            });
        }
    </script>
@endsection
