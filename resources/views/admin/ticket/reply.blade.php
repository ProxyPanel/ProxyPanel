@extends('admin.layouts')
@section('content')
    <div class="page-content">
        <x-ui.panel type="bordered" title-class="cyan-600" icon="wb-help-circle" :title="$ticket->title">
            <x-slot:actions>
                <div class="btn-group">
                    <button class="btn icon-1x btn-info btn-icon wb-user-circle" data-target="#userInfo" data-toggle="modal" type="button">
                        {{ trans('admin.ticket.user_info') }}</button>
                    <a class="btn btn-default" href="{{ route('admin.ticket.index') }}">{{ trans('common.back') }}</a>
                    @if ($ticket->status !== 2)
                        @can('admin.ticket.destroy')
                            <button class="btn btn-danger" onclick="closeTicket()"> {{ trans('common.close') }} </button>
                        @endcan
                    @endif
                </div>
            </x-slot:actions>

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

            @can('admin.ticket.update')
                <x-slot:footer>
                    <div class="pb-30">
                        <form>
                            <div class="input-group">
                                <input class="form-control" id="editor" type="text" placeholder="{{ trans('user.ticket.reply_placeholder') }}" />
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" onclick="replyTicket()"> {{ trans('common.send') }}</button>
                                </span>
                            </div>
                        </form>
                    </div>
                </x-slot:footer>
            @endcan
        </x-ui.panel>
    </div>

    <x-ui.modal id="userInfo" position="sidebar" :title="trans('admin.ticket.user_info')">
        <x-slot:header>
            <h4 class="modal-title">
                <i class="wb-user" aria-hidden="true"></i> {{ trans('admin.ticket.user_info') }}
            </h4>
        </x-slot:header>

        <ul class="list-group list-group-dividered px-20 mb-0">
            <h5>{{ trans('admin.node.info.basic') }}</h5>
            <dl class="dl-horizontal row">
                <dt class="col-sm-3">{{ trans('model.user.nickname') }}</dt>
                <dd class="col-sm-9">{{ $user->nickname }}</dd>
                <dt class="col-sm-3">{{ trans('model.user.username') }}</dt>
                <dd class="col-sm-9">{{ $user->username }}</dd>
                <dt class="col-sm-3">{{ trans('model.user.account_status') }}</dt>
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
                <dt class="col-sm-3">{{ trans('model.common.level') }}</dt>
                <dd class="col-sm-9">{{ $user->level }}</dd>
                <dt class="col-sm-3">{{ trans('model.user_group.attribute') }}</dt>
                <dd class="col-sm-9">{{ $user->userGroup->name ?? trans('common.none') }}</dd>
                <dt class="col-sm-3">{{ trans('model.user.credit') }}</dt>
                <dd class="col-sm-9">{{ $user->credit }}</dd>
                <dt class="col-sm-3">{{ trans('model.user.traffic_used') }}</dt>
                <dd class="col-sm-9">{{ formatBytes($user->used_traffic) }}
                    / {{ $user->transfer_enable_formatted }}</dd>
                <dt class="col-sm-3">{{ trans('model.user.reset_date') }}</dt>
                <dd class="col-sm-9">{{ $user->reset_date ?? trans('common.none') }}</dd>
                <dt class="col-sm-3">{{ trans('common.latest_at') }}</dt>
                <dd class="col-sm-9">
                    {{ $user->t ? date('Y-m-d H:i', $user->t) : trans('common.status.unused') }}
                </dd>
                <dt class="col-sm-3">{{ trans('model.user.expired_date') }}</dt>
                <dd class="col-sm-9">
                    @if ($user->expiration_status !== 3)
                        <span class="badge badge-lg badge-{{ ['danger', 'warning', 'default'][$user->expiration_status] }}">
                            {{ $user->expiration_date }} </span>
                    @else
                        {{ $user->expiration_date }}
                    @endif
                </dd>
                <dt class="col-sm-3">{{ trans('model.user.remark') }}</dt>
                <dt class="col-sm-3">{!! $user->remark !!}</dt>
            </dl>
            <h5>{{ trans('admin.user.info.proxy') }}</h5>
            <dl class="dl-horizontal row">
                <dt class="col-sm-3">{{ trans('common.status.attribute') }}</dt>
                <dd class="col-sm-9">
                    <span class="badge badge-lg badge-{{ $user->enable ? 'info' : 'danger' }}">
                        <i class="wb-{{ $user->enable ? 'check' : 'close' }}" aria-hidden="true"></i>
                    </span>
                </dd>
                <dt class="col-sm-3">{{ trans('model.user.port') }}</dt>
                <dd class="col-sm-9">{!! $user->port ?: '<span class="badge badge-lg badge-danger"> ' . trans('common.none') . ' </span>' !!}</dd>
            </dl>
            <h5>{{ trans('common.more') }}</h5>
            <dl class="dl-horizontal row">
                <dt class="col-sm-3">{{ trans('admin.ticket.inviter_info') }}</dt>
                <dd class="col-sm-9">
                    {{ $user->inviter->nickname ?? trans('common.none') }}
                </dd>
                @isset($user->inviter)
                    <dt class="col-sm-3 offset-md-1">{{ trans('model.user.username') }}</dt>
                    <dd class="col-sm-8">{{ $user->inviter->username }}</dd>
                    <dt class="col-sm-3 offset-md-1">{{ trans('model.common.level') }}</dt>
                    <dd class="col-sm-8">{{ $user->inviter->level }}</dd>
                    <dt class="col-sm-3 offset-md-1">{{ trans('common.latest_at') }}</dt>
                    <dd class="col-sm-8">
                        {{ $user->inviter->t ? date('Y-m-d H:i', $user->inviter->t) : trans('common.status.unused') }}
                    </dd>
                @endisset
            </dl>
        </ul>

        <x-slot:footer>
            <button class="btn btn-default" data-dismiss="modal" type="button">{{ trans('common.close') }}</button>
        </x-slot:footer>
    </x-ui.modal>
@endsection
@section('javascript')
    <script>
        @can('admin.ticket.destroy')
            // 关闭工单
            function closeTicket() {
                showConfirm({
                    title: '{{ trans('admin.ticket.close_confirm') }}',
                    onConfirm: function() {
                        ajaxDelete('{{ route('admin.ticket.destroy', $ticket->id) }}', {}, {
                            success: function(ret) {
                                handleResponse(ret, {
                                    redirectUrl: '{{ route('admin.ticket.index') }}'
                                });
                            }
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
                    showMessage({
                        title: '{{ ucfirst(trans('validation.required', ['attribute' => trans('validation.attributes.content')])) }}!',
                        icon: 'warning',
                        timer: 1500,
                    });
                    return false;
                }

                showConfirm({
                    title: '{{ trans('user.ticket.reply_confirm') }}',
                    onConfirm: function() {
                        ajaxPut('{{ route('admin.ticket.update', $ticket) }}', {
                            content: content
                        });
                    }
                });
            }
        @endcan
    </script>
@endsection
