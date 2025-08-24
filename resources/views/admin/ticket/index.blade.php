@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.customer_service.ticket')" :theads="['#', trans('model.user.username'), ucfirst(trans('validation.attributes.title')), trans('common.status.attribute'), trans('common.action')]" :count="trans('admin.ticket.counts', ['num' => $ticketList->total()])" :pagination="$ticketList->links()">
            @can('admin.ticket.store')
                <x-slot:actions>
                    <button class="btn btn-primary btn-animate btn-animate-side" data-toggle="modal" data-target="#add_ticket_modal">
                        <span>
                            <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('user.ticket.new') }}
                        </span>
                    </button>
                </x-slot:actions>
            @endcan
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-3 col-sm-6" name="username" :placeholder="trans('model.user.username')" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($ticketList as $ticket)
                    <tr>
                        <td> {{ $ticket->id }} </td>
                        <td>
                            @if (!$ticket->user)
                                【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                            @else
                                @can('admin.user.index')
                                    <a href="{{ route('admin.user.index', ['id' => $ticket->user->id]) }}" target="_blank">{{ $ticket->user->username }}</a>
                                @else
                                    {{ $ticket->user->username }}
                                @endcan
                            @endif
                        </td>

                        <td>
                            {{ $ticket->title }}
                        </td>
                        <td>
                            {!! $ticket->status_label !!}
                        </td>
                        <td>
                            @can('admin.ticket.edit')
                                <a class="btn btn-animate btn-animate-vertical btn-outline-info" href="{{ route('admin.ticket.edit', $ticket) }}">
                                    <span>
                                        @if ($ticket->status === 2)
                                            <i class="icon wb-eye" aria-hidden="true" style="left: 40%"> </i>{{ trans('common.view') }}
                                        @else
                                            <i class="icon wb-check" aria-hidden="true" style="left: 40%"> </i>{{ trans('common.open') }}
                                        @endif
                                    </span>
                                </a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>

    @can('admin.ticket.store')
        <x-ui.modal id="add_ticket_modal" :title="trans('user.ticket.new')" size="lg" position="center" :keyboard="false">
            <div class="form-group row">
                <label class="col-2 col-form-label" for="userId">{{ trans('model.user.attribute') }}</label>
                <div class="input-group col-10">
                    <input class="form-control col-md-4" id="uid" name="uid" type="number" placeholder="{{ trans('model.user.id') }}" />
                    <div class="input-group-prepend">
                        <span class="input-group-text">{{ trans('common.or') }}</span>
                    </div>
                    <input class="form-control col-md-8" id="username" name="username" type="text" placeholder="{{ trans('model.user.username') }}" />
                </div>
            </div>
            <div class="form-group">
                <input class="form-control" id="title" name="title" type="text" placeholder="{{ ucfirst(trans('validation.attributes.title')) }}">
            </div>
            <div class="form-group">
                <textarea class="form-control" id="content" name="content" type="text" rows="5" placeholder="{{ ucfirst(trans('validation.attributes.content')) }}"></textarea>
            </div>

            <x-slot:actions>
                <button class="btn btn-success" data-dismiss="modal" type="button" onclick="createTicket()"> {{ trans('common.confirm') }} </button>
            </x-slot:actions>
        </x-ui.modal>
    @endcan
@endsection
@push('javascript')
    <script>
        @can('admin.ticket.store')
            // 发起工单
            function createTicket() {
                const uid = $("#uid").val();
                const username = $("#username").val();
                const title = $("#title").val();
                const content = $("#content").val();

                if (uid.trim() === "" && username.trim() === "") {
                    showMessage({
                        title: '{{ trans('admin.ticket.send_to') }}',
                        icon: "warning"
                    });
                    return false;
                }

                if (title.trim() === "") {
                    showMessage({
                        title: '{{ ucfirst(trans('validation.required', ['attribute' => trans('validation.attributes.title')])) }}',
                        icon: "warning"
                    });
                    return false;
                }

                if (content.trim() === "") {
                    showMessage({
                        title: '{{ ucfirst(trans('validation.required', ['attribute' => trans('validation.attributes.content')])) }}',
                        icon: "warning"
                    });
                    return false;
                }

                showConfirm({
                    title: '{{ trans('user.ticket.submit_tips') }}',
                    onConfirm: function() {
                        ajaxPost("{{ route('admin.ticket.store') }}", {
                            uid: uid,
                            username: username,
                            title: title,
                            content: content
                        }, {
                            success: function(ret) {
                                $("#add_ticket_modal").modal("hide");
                                handleResponse(ret);
                            },
                            error: function(xhr) {
                                $("#add_ticket_modal").modal("hide");
                                handleErrors(xhr);
                            }
                        });
                    }
                });
            }
        @endcan
    </script>
@endpush
